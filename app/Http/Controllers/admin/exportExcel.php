<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\ReportExport;
use App\Exports\ReportExportRider;
use Maatwebsite\Excel\Facades\Excel;

class exportExcel extends Controller
{
    public function exportExcel()
    {
        return Excel::download(new ReportExport, 'report.xlsx');
    }

    public function exportExcelRider()
    {
        return Excel::download(new ReportExportRider, 'report.xlsx');
    }
}
