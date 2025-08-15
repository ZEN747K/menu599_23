<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\Menu as ModelsMenu;
use App\Models\MenuFiles;
use App\Models\MenuOption;
use App\Models\MenuTypeOption;
use Illuminate\Http\Request;

class Menu extends Controller
{
    public function menu()
    {
        $data['function_key'] = __FUNCTION__;
        return view('menu.index', $data);
    }


   public function menulistData()
{
    $data = [
        'status' => false,
        'message' => '',
        'data' => []
    ];
    $menu = ModelsMenu::with('category')->get();

    if (count($menu) > 0) {
        $info = [];
        foreach ($menu as $rs) {
            // ตรวจสอบสถานะการขายตามเวลาปัจจุบัน
            $isCurrentlyAvailable = $rs->isAvailable();
            $availabilityMessage = $rs->getAvailabilityMessage();
            
            // สถานะการขาย - เปลี่ยนตามเวลา
            $statusBadges = '';
            
            // ตรวจสอบสถานะหลัก และเวลาขาย
            if ($rs->is_active && $isCurrentlyAvailable) {
                $statusBadges .= '<span class="badge bg-success me-1">เปิดขาย</span>';
            } elseif (!$rs->is_active) {
                $statusBadges .= '<span class="badge bg-danger me-1">ปิดขาย (ตั้งค่า)</span>';
            } elseif ($rs->is_active && !$isCurrentlyAvailable && $rs->has_time_restriction) {
                $statusBadges .= '<span class="badge bg-warning text-dark me-1">ปิดขาย (เวลา)</span>';
            } else {
                $statusBadges .= '<span class="badge bg-danger me-1">ปิดขาย</span>';
            }
            
            // สถานะสต็อก
            if ($rs->is_out_of_stock) {
                $statusBadges .= '<span class="badge bg-danger me-1">สินค้าหมด</span>';
            }
            
            // การจำกัดเวลา
            if ($rs->has_time_restriction) {
                $statusBadges .= '<span class="badge bg-info me-1">จำกัดเวลา</span>';
            }
            
            // ข้อมูลเวลาขาย
            $timeInfo = '';
            if ($rs->has_time_restriction) {
                $timeInfo = '<div class="small text-muted">';
                if ($rs->available_from && $rs->available_until) {
                    $timeInfo .= 'เวลา: ' . $rs->available_from->format('H:i') . ' - ' . $rs->available_until->format('H:i') . '<br>';
                }
                if ($rs->available_days) {
                    $timeInfo .= 'วัน: ' . $rs->getAvailableDaysText();
                }
                $timeInfo .= '</div>';
            }
            
            // ปุ่มสลับสถานะ
            $statusToggle = '
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm ' . ($rs->is_active ? 'btn-success' : 'btn-danger') . ' toggle-status" 
                            data-id="' . $rs->id . '" data-field="is_active" data-value="' . ($rs->is_active ? 0 : 1) . '"
                            title="' . ($rs->is_active ? 'ปิดขาย' : 'เปิดขาย') . '">
                        <i class="bx ' . ($rs->is_active ? 'bx-toggle-right' : 'bx-toggle-left') . '"></i>
                    </button>
                    <button type="button" class="btn btn-sm ' . ($rs->is_out_of_stock ? 'btn-warning' : 'btn-outline-secondary') . ' toggle-status" 
                            data-id="' . $rs->id . '" data-field="is_out_of_stock" data-value="' . ($rs->is_out_of_stock ? 0 : 1) . '"
                            title="' . ($rs->is_out_of_stock ? 'มีสินค้า' : 'สินค้าหมด') . '">
                        <i class="bx ' . ($rs->is_out_of_stock ? 'bx-package' : 'bx-check-circle') . '"></i>
                    </button>
                </div>
            ';
            
            // เพิ่มปุ่มแก้ไขสต็อกถ้าต้องการ
            $stockButton = '';
            if ($rs->stock_quantity !== null) {
                $stockButton = '
                    <button type="button" class="btn btn-sm btn-outline-primary edit-stock ms-1" 
                            data-id="' . $rs->id . '" data-stock="' . $rs->stock_quantity . '"
                            title="แก้ไขจำนวนสต็อก">
                        <i class="bx bx-edit"></i>
                    </button>
                ';
            }
            
            $option = '<a href="' . route('menuTypeOption', $rs->id) . '" class="btn btn-sm btn-outline-primary" title="ตัวเลือก"><i class="bx bx-list-check"></i></a>';
            $action = '<a href="' . route('menuEdit', $rs->id) . '" class="btn btn-sm btn-outline-primary" title="แก้ไข"><i class="bx bx-edit-alt"></i></a>
            <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-danger deleteMenu" title="ลบ"><i class="bx bxs-trash"></i></button>';
            
            // กำหนดสีสำหรับสถานะการขาย
            $availabilityClass = '';
            if ($isCurrentlyAvailable && $rs->is_active && !$rs->is_out_of_stock) {
                $availabilityClass = 'text-success fw-bold';
            } elseif (!$isCurrentlyAvailable && $rs->has_time_restriction) {
                $availabilityClass = 'text-warning fw-bold';
            } else {
                $availabilityClass = 'text-danger fw-bold';
            }
            
            $info[] = [
                'name' => $rs->name . $timeInfo,
                'category' => $rs['category']->name,
                'price' => number_format($rs->base_price, 2) . ' ฿',
                'status' => $statusBadges,
                'stock_quantity' => ($rs->stock_quantity !== null ? $rs->stock_quantity : 'ไม่จำกัด') . $stockButton,
                'availability' => '<span class="' . $availabilityClass . '">' . $availabilityMessage . '</span>',
                'controls' => $statusToggle,
                'option' => $option,
                'action' => $action
            ];
        }
        $data = [
            'data' => $info,
            'status' => true,
            'message' => 'success'
        ];
    }
    return response()->json($data);
}

    public function MenuCreate()
    {
        $data['function_key'] = 'menu';
        $data['category'] = Categories::get();
        return view('menu.create', $data);
    }

    public function menuSave(Request $request)
{
    $input = $request->input();
    
    if (!isset($input['id'])) {
        // สร้างเมนูใหม่
        $menu = new ModelsMenu();
        $menu->name = $input['name'];
        $menu->categories_id = $input['categories_id'];
        $menu->base_price = $input['base_price'];
        $menu->detail = $input['detail'];
        
        // ข้อมูลการเปิดปิดและตั้งเวลา
        $menu->is_active = $input['is_active'] ?? 1;
        $menu->is_out_of_stock = $input['is_out_of_stock'] ?? 0;
        $menu->stock_quantity = $input['stock_quantity'] ?: null;
        $menu->unavailable_message = $input['unavailable_message'] ?: null;
        $menu->has_time_restriction = isset($input['has_time_restriction']) ? 1 : 0;
        
        if ($menu->has_time_restriction) {
            $menu->available_from = $input['available_from'] ?: null;
            $menu->available_until = $input['available_until'] ?: null;
            $menu->available_days = !empty($input['available_days']) ? $input['available_days'] : null;
        }
        
        if ($menu->save()) {
            // บันทึกไฟล์รูปภาพ
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('image', $filename, 'public');

                $categories_file = new MenuFiles();
                $categories_file->menu_id = $menu->id;
                $categories_file->file = $path;
                $categories_file->save();
            }
            return redirect()->route('menu')->with('success', 'บันทึกรายการเรียบร้อยแล้ว');
        }
    } else {
        $menu = ModelsMenu::find($input['id']);
        $menu->name = $input['name'];
        $menu->categories_id = $input['categories_id'];
        $menu->base_price = $input['base_price'];
        $menu->detail = $input['detail'];
        
        $menu->is_active = $input['is_active'] ?? 1;
        $menu->is_out_of_stock = $input['is_out_of_stock'] ?? 0;
        $menu->stock_quantity = $input['stock_quantity'] ?: null;
        $menu->unavailable_message = $input['unavailable_message'] ?: null;
        $menu->has_time_restriction = isset($input['has_time_restriction']) ? 1 : 0;
        
        if ($menu->has_time_restriction) {
            $menu->available_from = $input['available_from'] ?: null;
            $menu->available_until = $input['available_until'] ?: null;
            $menu->available_days = !empty($input['available_days']) ? $input['available_days'] : null;
        } else {
            $menu->available_from = null;
            $menu->available_until = null;
            $menu->available_days = null;
        }
        
        if ($menu->save()) {
            if ($request->hasFile('file')) {
                $categories_file = MenuFiles::where('menu_id', $input['id'])->delete();

                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('image', $filename, 'public');

                $categories_file = new MenuFiles();
                $categories_file->menu_id = $menu->id;
                $categories_file->file = $path;
                $categories_file->save();
            }
            return redirect()->route('menu')->with('success', 'บันทึกรายการเรียบร้อยแล้ว');
        }
    }
    return redirect()->route('menu')->with('error', 'ไม่สามารถบันทึกข้อมูลได้');
}

public function toggleMenuStatus(Request $request)
{
    $data = [
        'status' => false,
        'message' => 'อัปเดตสถานะไม่สำเร็จ',
    ];
    
    $id = $request->input('id');
    $field = $request->input('field'); 
    $value = $request->input('value');
    
    if ($id && in_array($field, ['is_active', 'is_out_of_stock'])) {
        $menu = ModelsMenu::find($id);
        if ($menu) {
            $menu->$field = $value;
            if ($menu->save()) {
                $data = [
                    'status' => true,
                    'message' => 'อัปเดตสถานะเรียบร้อยแล้ว',
                ];
            }
        }
    }
    
    return response()->json($data);
}
    public function menuEdit($id)
    {
        $function_key = 'menu';
        $info = ModelsMenu::with('files', 'category')->find($id);
        $category = Categories::get();

        return view('menu.edit', compact('info', 'function_key', 'category'));
    }

    public function menuDelete(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'ลบข้อมูลไม่สำเร็จ',
        ];
        $id = $request->input('id');
        if ($id) {
            $delete = ModelsMenu::find($id);
            if ($delete->delete()) {
                $data = [
                    'status' => true,
                    'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
                ];
            }
        }

        return response()->json($data);
    }

    public function menuOption($id)
    {
        $data['function_key'] = 'menu';
        $data['id'] = $id;
        $data['info'] = MenuTypeOption::find($id);
        return view('menu.option.index', $data);
    }

    public function menulistOption(Request $request)
    {
        $id = $request->input('id');
        $data = [
            'status' => false,
            'message' => '',
            'data' => []
        ];
        $menuOption = MenuOption::where('menu_type_option_id', $id)->get();

        if (count($menuOption) > 0) {
            $info = [];
            foreach ($menuOption as $rs) {
                $stock = '<a href="' . route('menuOptionStock', $rs->id) . '" class="btn btn-sm btn-outline-primary"><i class="bx bx-list-ol"></i></a>';
                $action = '<a href="' . route('menuOptionEdit', $rs->id) . '" class="btn btn-sm btn-outline-primary" title="แก้ไข"><i class="bx bx-edit-alt"></i></a>
                <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-danger deleteMenu" title="ลบ"><i class="bx bxs-trash"></i></button>';
                $info[] = [
                    'name' => $rs->type,
                    'price' => $rs->price . ' บาท',
                    'stock' => $stock,
                    'action' => $action
                ];
            }
            $data = [
                'data' => $info,
                'status' => true,
                'message' => 'success'
            ];
        }
        return response()->json($data);
    }

    public function menulistOptionCreate($id)
    {
        $data['function_key'] = 'menu';
        $data['id'] = $id;
        return view('menu.option.create', $data);
    }

    public function menuOptionSave(Request $request)
    {
        $input = $request->input();
        $menu = new menuOption();
        $menu->type = $input['name'];
        $menu->price = ($input['price'] != '') ? $input['price'] : 0;
        $menu->menu_type_option_id = $input['menu_type_option_id'];
        if ($menu->save()) {
            return redirect()->route('menuOption', $input['menu_type_option_id'])->with('success', 'บันทึกรายการเรียบร้อยแล้ว');
        }
        return redirect()->route('menuOption', $input['menu_type_option_id'])->with('error', 'ไม่สามารถบันทึกข้อมูลได้');
    }

    public function menuOptionEdit($id)
    {
        $function_key = 'menu';
        $info = menuOption::find($id);

        return view('menu.option.edit', compact('info', 'function_key'));
    }

    public function menuOptionUpdate(Request $request)
    {
        $input = $request->input();
        $menu = menuOption::find($input['id']);
        $menu->type = $input['name'];
        $menu->price = ($input['price'] != '') ? $input['price'] : 0;
        if ($menu->save()) {
            return redirect()->route('menuOption', $menu->menu_type_option_id)->with('success', 'บันทึกรายการเรียบร้อยแล้ว');
        }
        return redirect()->route('menu')->with('error', 'ไม่สามารถบันทึกข้อมูลได้');
    }


    public function menuOptionDelete(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'ลบข้อมูลไม่สำเร็จ',
        ];
        $id = $request->input('id');
        if ($id) {
            $delete = menuOption::find($id);
            if ($delete->delete()) {
                $data = [
                    'status' => true,
                    'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
                ];
            }
        }

        return response()->json($data);
    }
    public function updateMenuStock(Request $request)
{
    $data = [
        'status' => false,
        'message' => 'อัปเดตสต็อกไม่สำเร็จ',
    ];
    
    $id = $request->input('id');
    $stockQuantity = $request->input('stock_quantity');
    
    if ($id) {
        $menu = ModelsMenu::find($id);
        if ($menu) {
            $menu->stock_quantity = ($stockQuantity === '' || $stockQuantity === null) ? null : (int)$stockQuantity;
            
            if ($menu->stock_quantity === null || $menu->stock_quantity > 0) {
                $menu->is_out_of_stock = false;
            }
            
            if ($menu->save()) {
                $data = [
                    'status' => true,
                    'message' => 'อัปเดตจำนวนสต็อกเรียบร้อยแล้ว',
                ];
            }
        }
    }
    
    return response()->json($data);
}

}
