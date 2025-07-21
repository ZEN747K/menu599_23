<?php

namespace App\Exports;

use App\Models\Orders;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ReportExportRider implements FromCollection, WithHeadings
{
    public function collection()
    {
        $item = Orders::select('pays.created_at', 'pays.payment_number', 'users.name', 'pays.total', 'pays.is_type')
            ->join('pay_groups', 'pay_groups.order_id', '=', 'orders.id')
            ->join('pays', 'pays.id', '=', 'pay_groups.pay_id')
            ->join('users', 'users.id', '=', 'orders.users_id')
            ->where('orders.status', 3)
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
        return $item;
    }

    public function headings(): array
    {
        return ['วันที่สั่ง', 'เลขที่ใบเสร็จ', 'ชื่อลูกค้า', 'ยอดรวม', 'รูปแบบการชำระ'];
    }
}
