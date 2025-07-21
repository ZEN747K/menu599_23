<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Controllers\admin\Category;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\LogStock;
use App\Models\Menu;
use App\Models\MenuOption;
use App\Models\MenuStock;
use App\Models\MenuTypeOption;
use App\Models\Orders;
use App\Models\OrdersDetails;
use App\Models\OrdersOption;
use App\Models\Promotion;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Config;
use App\Models\ConfigPromptpay;
use PromptPayQR\Builder as PromptPayQRBuilder;
use App\Models\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class Main extends Controller
{
    public function index(Request $request)
    {
        $table_id = $request->input('table');
        if ($table_id) {
            session(['table_id' => $table_id]);
        }
        $promotion = Promotion::where('is_status', 1)->get();
        $category = Categories::has('menu')->with('files')->get();
        return view('users.main_page', compact('category', 'promotion'));
    }

    public function detail($id)
    {
        $item = [];
        $menu = Menu::where('categories_id', $id)->with('files')->orderBy('created_at', 'asc')->get();
        foreach ($menu as $key => $rs) {
            $item[$key] = [
                'id' => $rs->id,
                'category_id' => $rs->categories_id,
                'name' => $rs->name,
                'detail' => $rs->detail,
                'base_price' => $rs->base_price,
                'files' => $rs['files']
            ];
            $typeOption = MenuTypeOption::where('menu_id', $rs->id)->get();
            if (count($typeOption) > 0) {
                foreach ($typeOption as $typeOptions) {
                    $optionItem = [];
                    $option = MenuOption::where('menu_type_option_id', $typeOptions->id)->get();
                    foreach ($option as $options) {
                        $optionItem[] = (object)[
                            'id' => $options->id,
                            'name' => $options->type,
                            'price' => $options->price
                        ];
                    }
                    $item[$key]['option'][$typeOptions->name] = [
                        'is_selected' => $typeOptions->is_selected,
                        'amout' => $typeOptions->amout,
                        'items' =>  $optionItem
                    ];
                }
            } else {
                $item[$key]['option'] = [];
            }
        }
        $menu = $item;
        return view('users.detail_page', compact('menu'));
    }

    public function order()
    {
        return view('users.list_page');
    }

    public function SendOrder(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'สั่งออเดอร์ไม่สำเร็จ',
        ];
        $orderData = $request->input('cart');
        $remark = $request->input('remark');
        $item = array();
        $total = 0;
        foreach ($orderData as $key => $order) {
            $item[$key] = [
                'menu_id' => $order['id'],
                'quantity' => $order['amount'],
                'price' => $order['total_price']
            ];
            if (!empty($order['options'])) {
                foreach ($order['options'] as $rs) {
                    $item[$key]['option'][] = $rs['id'];
                }
            } else {
                $item[$key]['option'] = [];
            }
            $total = $total + $order['total_price'];
        }
        if (!empty($item)) {
            $order = new Orders();
            $order->table_id = session('table_id') ?? '1';
            $order->total = $total;
            $order->remark = $remark;
            $order->status = 1;
            if ($order->save()) {
                foreach ($item as $rs) {
                    $orderdetail = new OrdersDetails();
                    $orderdetail->order_id = $order->id;
                    $orderdetail->menu_id = $rs['menu_id'];
                    $orderdetail->quantity = $rs['quantity'];
                    $orderdetail->price = $rs['price'];
                    if ($orderdetail->save()) {
                        foreach ($rs['option'] as $key => $option) {
                            $orderOption = new OrdersOption();
                            $orderOption->order_detail_id = $orderdetail->id;
                            $orderOption->option_id = $option;
                            $orderOption->save();
                            $menuStock = MenuStock::where('menu_option_id', $option)->get();
                            if ($menuStock->isNotEmpty()) {
                                foreach ($menuStock as $stock_rs) {
                                    $stock = Stock::find($stock_rs->stock_id);
                                    $stock->amount = $stock->amount - ($stock_rs->amount * $rs['qty']);
                                    if ($stock->save()) {
                                        $log_stock = new LogStock();
                                        $log_stock->stock_id = $stock_rs->stock_id;
                                        $log_stock->order_id = $order->id;
                                        $log_stock->menu_option_id = $rs['option'];
                                        $log_stock->old_amount = $stock_rs->amount;
                                        $log_stock->amount = ($stock_rs->amount * $rs['qty']);
                                        $log_stock->status = 2;
                                        $log_stock->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
            event(new OrderCreated(['📦 มีออเดอร์ใหม่']));
            $data = [
                'status' => true,
                'message' => 'สั่งออเดอร์เรียบร้อยแล้ว',
            ];
        }
        return response()->json($data);
    }

    public function sendEmp()
    {
        event(new OrderCreated(['ลูกค้าเรียกจากโต้ะที่ ' . session('table_id')]));
    }
      public function listorder()
    {
        $orderlist = [];
        $orderlist = Orders::where('table_id', session('table_id'))->whereIn('status', [1, 2])->get();
        $config = Config::first();
        $config_promptpay = ConfigPromptpay::where('config_id', $config->id)->first();
        $qr_code = '';
        if ($config_promptpay) {
            if ($config_promptpay->promptpay != '') {
                $qr_code = PromptPayQRBuilder::staticMerchantPresentedQR($config_promptpay->promptpay)->toSvgString();
                $qr_code = '<div class="row g-3 mb-3">
                    <div class="col-md-12">
                        ' . $qr_code . '
                    </div>
                </div>';
            }
        }
        if ($config->image_qr != '') {
            if ($qr_code == '') {
                $qr_code =  '<div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <img width="100%" src="' . url('storage/' . $config->image_qr) . '">
                    </div>
                </div>';
            }
        }
        return view('users.order', compact('orderlist', 'qr_code'));
    }

    public function listorderDetails(Request $request)
    {
        $groupedMenus = OrdersDetails::select('menu_id')
            ->where('order_id', $request->input('id'))
            ->groupBy('menu_id')
            ->get();
        $info = '';
        if ($groupedMenus->count() > 0) {
            foreach ($groupedMenus as $value) {
                $orderDetails = OrdersDetails::where('order_id', $request->input('id'))
                    ->where('menu_id', $value->menu_id)
                    ->with('menu', 'option')
                    ->get();
                $menuName = optional($orderDetails->first()->menu)->name ?? 'ไม่พบชื่อเมนู';
                $info .= '<div class="mb-3">';
                $info .= '<div class="row">';
                $info .= '<div class="col-auto d-flex align-items-start">';
                $info .= '</div>';
                $info .= '</div>';
                foreach ($orderDetails as $rs) {
                    $detailsText = $rs->option ? '+ ' . htmlspecialchars($rs->option->type) : '';
                    $priceTotal = number_format($rs->quantity * $rs->price, 2);
                    $info .= '<ul class="list-group mb-1 shadow-sm rounded">';
                    $info .= '<li class="list-group-item d-flex justify-content-between align-items-start">';
                    $info .= '<div class="">';
                    $info .= '<div><span class="fw-bold">' . htmlspecialchars($menuName) . '</span></div>';
                    if (!empty($detailsText)) {
                        $info .= '<div class="small text-secondary mb-1">' . $detailsText . '</div>';
                    }
                    $info .= '</div>';
                    $info .= '<div class="text-end d-flex flex-column align-items-end">';
                    $info .= '<div class="mb-1">จำนวน: ' . $rs->quantity . '</div>';
                    $info .= '<div>';
                    $info .= '<button class="btn btn-sm btn-primary">' . $priceTotal . ' บาท</button>';
                    $info .= '</div>';
                    $info .= '</div>';
                    $info .= '</li>';
                    $info .= '</ul>';
                }
                $info .= '</div>';
            }
        }
        echo $info;
    }
    public function confirmPay(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'สั่งออเดอร์ไม่สำเร็จ',
        ];
        $orderData = $request->input('orderData');
        $remark = $request->input('remark');
        $request->validate([
            'silp' => 'required|image|mimes:jpeg,png|max:2048',
        ]);
        $item = array();
        $total = 0;

        if (session('table_id')) {
            $order = Orders::where('table_id', session('table_id'))->whereIn('status', [1, 2])->get();
            foreach ($order as $value) {
                $value->status = 4;
                if ($request->hasFile('silp')) {
                    $file = $request->file('silp');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('image', $filename, 'public');
                    $value->image = $path;
                }
                if ($value->save()) {
                    foreach ($item as $rs) {
                        $orderdetail = new OrdersDetails();
                        $orderdetail->order_id = $order->id;
                        $orderdetail->menu_id = $rs['id'];
                        $orderdetail->option_id = $rs['option'];
                        $orderdetail->quantity = $rs['qty'];
                        $orderdetail->price = $rs['price'];
                        $orderdetail->save();
                    }
                }
            }
            event(new OrderCreated(['📦 มีออเดอร์ใหม่']));
            $data = [
                'status' => true,
                'message' => 'สั่งออเดอร์เรียบร้อยแล้ว',
            ];
        }
        return response()->json($data);
    }
}
