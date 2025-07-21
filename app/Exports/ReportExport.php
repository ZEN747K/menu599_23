<?php

namespace App\Exports;

use App\Models\Pay;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ReportExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $query =  Pay::select('created_at', 'payment_number', 'total', 'is_type')
            ->whereNotNull('table_id')
            ->get()
            ->map(function ($item) {
                $item->created_at = \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s');
                $item->is_type = match ($item->is_type) {
                    0 => 'เงินสด',
                    1 => 'เงินโอน',
                    default => 'ไม่ทราบประเภท',
                };
                return $item;
            });
        return $query;
    }

    public function headings(): array
    {
        return ['วันที่สั่ง', 'เลขที่ใบเสร็จ', 'ยอดรวม', 'รูปแบบการชำระ'];
    }
}
