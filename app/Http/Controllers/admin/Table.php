<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Table as ModelsTable;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Table extends Controller
{
    public function table()
    {
        $data['function_key'] = __FUNCTION__;
        return view('table.index', $data);
    }

    public function tablelistData()
    {
        $data = [
            'status' => false,
            'message' => '',
            'data' => []
        ];
        $table = ModelsTable::get();

        if (count($table) > 0) {
            $info = [];
            foreach ($table as $rs) {
                $qr_code = '<button data-id="' . $rs->id . '" type="button" class="btn btn-sm btn-outline-primary modalQr"><i class="bx bx-search-alt-2"></i></button>';
                $action = '<a href="' . route('tableEdit', $rs->id) . '" class="btn btn-sm btn-outline-primary" title="แก้ไข"><i class="bx bx-edit-alt"></i></a>
                <button type="button" data-id="' . $rs->id . '" class="btn btn-sm btn-outline-danger deleteTable" title="ลบ"><i class="bx bxs-trash"></i></button>';
                $info[] = [
                    'number' => $rs->table_number,
                    'qr_code' => $qr_code,
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

    public function tableCreate()
    {
        $data['function_key'] = 'table';
        return view('table.create', $data);
    }

    public function tableEdit($id)
    {
        $function_key = 'table';
        $info = ModelsTable::find($id);

        return view('table.edit', compact('info', 'function_key'));
    }

    public function tableSave(Request $request)
    {
        // เพิ่ม validation
        $request->validate([
            'table_number' => 'required|numeric|unique:tables,table_number,' . ($request->id ?? 'NULL') . ',id'
        ], [
            'table_number.required' => 'กรุณาใส่เลขโต้ะ',
            'table_number.numeric' => 'กรุณาใส่เฉพาะตัวเลข',
            'table_number.unique' => 'เลขโต้ะนี้มีอยู่ในระบบแล้ว'
        ]);

        $input = $request->input();
        
        try {
            if (!isset($input['id'])) {
                // สร้างโต้ะใหม่
                $table = new ModelsTable();
                $table->table_number = $input['table_number'];
                $table->qr_code = QrCode::size(300)->generate(url('?table=' . $input['table_number']));
                
                if ($table->save()) {
                    return redirect()->route('table')->with('success', 'เพิ่มโต้ะเรียบร้อยแล้ว');
                }
            } else {
                // แก้ไขโต้ะ
                $table = ModelsTable::find($input['id']);
                
                if (!$table) {
                    return redirect()->route('table')->with('error', 'ไม่พบข้อมูลโต้ะที่ต้องการแก้ไข');
                }
                
                $table->table_number = $input['table_number'];
                $table->qr_code = QrCode::size(300)->generate(url('?table=' . $input['table_number']));
                
                if ($table->save()) {
                    return redirect()->route('table')->with('success', 'แก้ไขข้อมูลโต้ะเรียบร้อยแล้ว');
                }
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                                  ->withInput();
        }
        
        return redirect()->route('table')->with('error', 'ไม่สามารถบันทึกข้อมูลได้');
    }

    public function QRshow(Request $request)
    {
        $info = '';
        $id = $request->input('id');
        $table = ModelsTable::find($id);

        if ($table && $id) {
            $info = $table->qr_code;
        } else {
            $info = '<div class="alert alert-warning">ไม่พบข้อมูล QR Code</div>';
        }
        
        echo $info;
    }

    public function tableDelete(Request $request)
    {
        $data = [
            'status' => false,
            'message' => 'ลบข้อมูลไม่สำเร็จ',
        ];
        
        $id = $request->input('id');
        
        if ($id) {
            try {
                $table = ModelsTable::find($id);
                
                if (!$table) {
                    $data['message'] = 'ไม่พบข้อมูลโต้ะที่ต้องการลบ';
                    return response()->json($data);
                }
                
                $hasOrders = \App\Models\Orders::where('table_id', $id)
                    ->whereIn('status', [1, 2]) 
                    ->exists();
                
                if ($hasOrders) {
                    $data['message'] = 'ไม่สามารถลบโต้ะได้ เนื่องจากมีออเดอร์ที่ยังไม่เสร็จสิ้น';
                    return response()->json($data);
                }
                
                if ($table->delete()) {
                    $data = [
                        'status' => true,
                        'message' => 'ลบข้อมูลโต้ะเรียบร้อยแล้ว',
                    ];
                }
            } catch (\Exception $e) {
                $data['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            }
        }

        return response()->json($data);
    }

    public function checkTableExists(Request $request)
    {
        $tableNumber = $request->input('table_number');
        $excludeId = $request->input('exclude_id'); 
        
        $query = ModelsTable::where('table_number', $tableNumber);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $exists = $query->exists();
        
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'โต้ะนี้มีอยู่ในระบบแล้ว' : 'โต้ะนี้ใช้ได้'
        ]);
    }

    public function getTableStatistics()
    {
        try {
            $totalTables = ModelsTable::count();
            $activeTables = \App\Models\Orders::whereNotNull('table_id')
                ->whereIn('status', [1, 2])
                ->distinct('table_id')
                ->count();
            
            $availableTables = $totalTables - $activeTables;
            
            return response()->json([
                'status' => true,
                'data' => [
                    'total' => $totalTables,
                    'active' => $activeTables,
                    'available' => $availableTables,
                    'usage_percent' => $totalTables > 0 ? round(($activeTables / $totalTables) * 100, 2) : 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'ไม่สามารถดึงข้อมูลสถิติได้'
            ]);
        }
    }

    public function downloadQRCode($id)
    {
        try {
            $table = ModelsTable::find($id);
            
            if (!$table) {
                abort(404, 'ไม่พบข้อมูลโต้ะ');
            }
            
            $qrCode = QrCode::size(300)
                ->format('png')
                ->generate(url('?table=' . $table->table_number));
            
            $fileName = 'table_' . $table->table_number . '_qr.png';
            
            return response($qrCode)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
                
        } catch (\Exception $e) {
            abort(500, 'ไม่สามารถสร้าง QR Code ได้');
        }
    }

    
    public function searchTables(Request $request)
    {
        $search = $request->input('search');
        
        $tables = ModelsTable::when($search, function($query) use ($search) {
            return $query->where('table_number', 'like', '%' . $search . '%');
        })->get();
        
        $data = [];
        foreach ($tables as $table) {
            $data[] = [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'qr_url' => route('downloadQRCode', $table->id)
            ];
        }
        
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}