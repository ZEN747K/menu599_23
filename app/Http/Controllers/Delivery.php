<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
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
use App\Models\User;
use App\Models\UsersAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class Delivery extends Controller
{
    public function index(Request $request)
    {
        $table_id = $request->input('table');
        if ($table_id) {
            session(['table_id' => $table_id]);
        }
        $promotion = Promotion::where('is_status', 1)->get();
        $category = Categories::has('menu')->with('files')->get();
        return view('delivery.main_page', compact('category', 'promotion'));
    }

    public function login()
    {
        return view('userslogin');
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
        return view('delivery.detail_page', compact('menu'));
    }

    public function order()
    {
        $address = [];
        if (Session::get('user')) {
            $address = UsersAddress::where('users_id', Session::get('user')->id)->get();
        }
        return view('delivery.list_page', compact('address'));
    }

    public function SendOrder(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'สั่งออเดอร์ไม่สำเร็จ',
        ];
        if (Session::get('user')) {
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
                $info = UsersAddress::where('is_use', 1)->where('users_id', Session::get('user')->id)->first();
                if ($info != null) {
                    $order = new Orders();
                    $order->users_id = Session::get('user')->id;
                    $order->address_id = $info->id;
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
                } else {
                    $data = [
                        'status' => false,
                        'message' => 'กรุณาเพิ่มที่อยู่จัดส่ง',
                    ];
                }
            }
        } else {
            $data = [
                'status' => false,
                'message' => 'กรุณาล็อกอินเพื่อสั่งอาหาร',
            ];
        }
        return response()->json($data);
    }

    public function sendEmp()
    {
        event(new OrderCreated(['ลูกค้าเรียกจากโต้ะที่ ' . session('table_id')]));
    }

    public function users()
    {
        $address = UsersAddress::where('users_id', Session::get('user')->id)->get();
        return view('delivery.users', compact('address'));
    }

    public function createaddress()
    {
        return view('delivery.address');
    }

    public function addressSave(Request $request)
    {
        $input = $request->post();

        if (!isset($input['id'])) {
            $address = new UsersAddress();
            $address->users_id = Session::get('user')->id;
            $address->name = $input['name'];
            $address->lat = $input['lat'];
            $address->long = $input['lng'];
            $address->tel = $input['tel'];
            $address->detail = $input['detail'];
            $address->is_use = 0;
            if ($address->save()) {
                return redirect()->route('delivery.users')->with('success', 'เพิ่มที่อยู่เรียบร้อยแล้ว');
            }
        } else {
            $address = UsersAddress::find($input['id']);
            $address->name = $input['name'];
            $address->lat = $input['lat'];
            $address->long = $input['lng'];
            $address->tel = $input['tel'];
            $address->detail = $input['detail'];
            if ($address->save()) {
                return redirect()->route('delivery.users')->with('success', 'แก้ไขที่อยู่เรียบร้อยแล้ว');
            }
        }

        return redirect()->route('delivery.users')->with('error', 'ไม่สามารถเพิ่มที่อยู่ได้');
    }

    public function change(Request $request)
    {
        $input = $request->post();
        $address = UsersAddress::where('users_id', Session::get('user')->id)->get();
        foreach ($address as $rs) {
            $rs->is_use = 0;
            $rs->save();
        }
        $address = UsersAddress::find($input['id']);
        $address->is_use = 1;
        $address->save();
    }

    public function editaddress($id)
    {
        $info = UsersAddress::find($id);
        return view('delivery.editaddress', compact('info'));
    }

    public function usersSave(Request $request)
    {
        $input = $request->post();
        $users = User::find(Session::get('user')->id);
        $users->name = $input['name'];
        $users->email = $input['email'];
        if ($users->save()) {
            Session::put('user', $users);
            return redirect()->route('delivery.users')->with('success', 'เพิ่มที่อยู่เรียบร้อยแล้ว');
        }
        return redirect()->route('delivery.users')->with('error', 'ไม่สามารถเพิ่มที่อยู่ได้');
    }

    public function listorder()
    {
        $orderlist = [];
        if (Session::get('user')) {
            $orderlist = Orders::select('orders.*', 'users.name', 'users.tel')
                ->where('users_id', Session::get('user')->id)
                ->leftJoin('rider_sends', 'orders.id', '=', 'rider_sends.order_id')
                ->leftJoin('users', 'rider_sends.rider_id', '=', 'users.id')
                ->get();
        }
        return view('delivery.order', compact('orderlist'));
    }

    public function listOrderDetail(Request $request)
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
                    ->with('menu', 'option.option')
                    ->get();
                $menuName = optional($orderDetails->first()->menu)->name ?? 'ไม่พบชื่อเมนู';
                $info .= '<div class="mb-3">';
                $info .= '<div class="row">';
                $info .= '<div class="col-auto d-flex align-items-start">';
                $info .= '</div>';
                $info .= '</div>';
                $detailsText = '';
                foreach ($orderDetails as $rs) {

                    $priceTotal = number_format($rs->quantity * $rs->price, 2);
                    $info .= '<ul class="list-group mb-1 shadow-sm rounded">';
                    $info .= '<li class="list-group-item d-flex justify-content-between align-items-start">';
                    $info .= '<div class="">';
                    $info .= '<div><span class="fw-bold">' . htmlspecialchars($menuName) . '</span></div>';
                    if (!empty($rs->option)) {
                        foreach ($rs->option as $value) {
                            $detailsText = $rs->option ? '+ ' . htmlspecialchars($value->option->type) : '';
                            $info .= '<div class="small text-secondary" style="text-align:start;">' . $detailsText . '</div>';
                        }
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

    public function register()
    {
        return view('usersRegister');
    }

    public function UsersRegister(Request $request)
    {
        $input = $request->input();
        $users = new User;
        $users->name = $input['name'];
        $users->tel = $input['tel'];
        $users->email = $input['email'];
        $users->password = Hash::make($input['password']);
        $users->email_verified_at = now();
        if ($users->save()) {
            return redirect()->route('delivery.login')->with('success', 'สมัครสมาชิกเรียบร้อยแล้ว');
        }
        return redirect()->route('delivery.register')->with('error', 'สมัครสมาชิกไม่สำเร็จ');
    }
}
