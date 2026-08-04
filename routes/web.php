<?php

use App\Http\Controllers\PayslipPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('components.layouts.app');
});

// Export/Preview Routes
Route::get('/pdf/payslip/{payslip}', [PayslipPdfController::class, 'downloadPayslip'])->name('pdf.payslip');
Route::get('/pdf/employee-report/{employee}', [PayslipPdfController::class, 'downloadEmployeeReport'])->name('pdf.employee-report');
Route::get('/export/payslip/{payslip}/xlsx', [PayslipPdfController::class, 'exportPayslipXlsx'])->name('export.payslip.xlsx');
Route::get('/export/payslip/{payslip}/jpeg', [PayslipPdfController::class, 'exportPayslipJpeg'])->name('export.payslip.jpeg');
Route::get('/export/employee-report/{employee}/xlsx', [PayslipPdfController::class, 'exportEmployeeReportXlsx'])->name('export.employee-report.xlsx');
Route::get('/export/employee-report/{employee}/jpeg', [PayslipPdfController::class, 'exportEmployeeReportJpeg'])->name('export.employee-report.jpeg');
