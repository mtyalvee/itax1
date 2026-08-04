<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PayslipPdfController extends Controller
{
    /**
     * Download a single payslip as a PDF document.
     */
    public function downloadPayslip(Payslip $payslip, Request $request)
    {
        $payslip->load('employee');

        // Fetch Accumulated Previous Payslips YTD (excluding current)
        $year = $payslip->period_end->format('Y');
        $prevAccumulatedQuery = Payslip::where('employee_id', $payslip->employee_id)
            ->whereYear('period_end', $year)
            ->where('period_end', '<', $payslip->period_start)
            ->get();

        $prevAccumulated = [
            'gross_pay' => $prevAccumulatedQuery->sum('gross_pay'),
            'paye' => $prevAccumulatedQuery->sum('paye'),
            'usc' => $prevAccumulatedQuery->sum('usc'),
            'prsi' => $prevAccumulatedQuery->sum('prsi'),
            'employer_prsi' => $prevAccumulatedQuery->sum('employer_prsi'),
            'net_pay' => $prevAccumulatedQuery->sum('net_pay'),
            'has_records' => $prevAccumulatedQuery->isNotEmpty(),
        ];

        // Fetch Accumulated Year-to-Date up to current payslip
        $accumulatedQuery = Payslip::where('employee_id', $payslip->employee_id)
            ->whereYear('period_end', $year)
            ->where('period_end', '<=', $payslip->period_end)
            ->get();

        $accumulated = [
            'gross_pay' => $accumulatedQuery->sum('gross_pay'),
            'paye' => $accumulatedQuery->sum('paye'),
            'usc' => $accumulatedQuery->sum('usc'),
            'prsi' => $accumulatedQuery->sum('prsi'),
            'employer_prsi' => $accumulatedQuery->sum('employer_prsi'),
            'net_pay' => $accumulatedQuery->sum('net_pay'),
        ];

        $pdf = Pdf::loadView('pdf.payslip', [
            'payslip' => $payslip,
            'employee' => $payslip->employee,
            'prevAccumulated' => $prevAccumulated,
            'accumulated' => $accumulated,
        ]);

        $filename = 'Payslip_' . str_replace(' ', '_', $payslip->employee->name) . '_' . $payslip->period_start->format('Ymd') . '.pdf';

        if ($request->has('download')) {
            return $pdf->download($filename);
        }
        return $pdf->stream($filename);
    }

    /**
     * Download a full employee report as a PDF document.
     */
    public function downloadEmployeeReport(Employee $employee, Request $request)
    {
        $payslips = $employee->payslips()->orderBy('period_end', 'desc')->get();

        // Calculate YTD accumulated totals
        $currentYear = now()->year;
        $ytdPayslips = $employee->payslips()
            ->whereYear('period_end', $currentYear)
            ->get();

        $accumulated = [
            'gross_pay' => $ytdPayslips->sum('gross_pay'),
            'paye' => $ytdPayslips->sum('paye'),
            'usc' => $ytdPayslips->sum('usc'),
            'prsi' => $ytdPayslips->sum('prsi'),
            'employer_prsi' => $ytdPayslips->sum('employer_prsi'),
            'net_pay' => $ytdPayslips->sum('net_pay'),
        ];

        $pdf = Pdf::loadView('pdf.employee-report', [
            'employee' => $employee,
            'payslips' => $payslips,
            'accumulated' => $accumulated,
            'year' => $currentYear,
        ]);

        $filename = 'Employee_Report_' . str_replace(' ', '_', $employee->name) . '_' . now()->format('Ymd') . '.pdf';

        if ($request->has('download')) {
            return $pdf->download($filename);
        }
        return $pdf->stream($filename);
    }

    /**
     * Export a single payslip as an XLSX (CSV) document.
    /**
     * Export a single payslip as an XLSX (CSV) document.
     */
    public function exportPayslipXlsx(Payslip $payslip)
    {
        $payslip->load('employee');

        // Fetch Accumulated Previous Payslips YTD (excluding current)
        $year = $payslip->period_end->format('Y');
        $prevAccumulatedQuery = Payslip::where('employee_id', $payslip->employee_id)
            ->whereYear('period_end', $year)
            ->where('period_end', '<', $payslip->period_start)
            ->get();

        $prevAccumulated = [
            'gross_pay' => $prevAccumulatedQuery->sum('gross_pay'),
            'paye' => $prevAccumulatedQuery->sum('paye'),
            'usc' => $prevAccumulatedQuery->sum('usc'),
            'prsi' => $prevAccumulatedQuery->sum('prsi'),
            'employer_prsi' => $prevAccumulatedQuery->sum('employer_prsi'),
            'net_pay' => $prevAccumulatedQuery->sum('net_pay'),
            'has_records' => $prevAccumulatedQuery->isNotEmpty(),
        ];

        // Fetch Accumulated Year-to-Date up to current payslip
        $accumulatedQuery = Payslip::where('employee_id', $payslip->employee_id)
            ->whereYear('period_end', $year)
            ->where('period_end', '<=', $payslip->period_end)
            ->get();

        $accumulated = [
            'gross_pay' => $accumulatedQuery->sum('gross_pay'),
            'paye' => $accumulatedQuery->sum('paye'),
            'usc' => $accumulatedQuery->sum('usc'),
            'prsi' => $accumulatedQuery->sum('prsi'),
            'employer_prsi' => $accumulatedQuery->sum('employer_prsi'),
            'net_pay' => $accumulatedQuery->sum('net_pay'),
        ];

        $filename = 'Payslip_' . str_replace(' ', '_', $payslip->employee->name) . '_' . $payslip->period_start->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($payslip, $prevAccumulated, $accumulated) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['PAYSLIP STATEMENT - iTax']);
            fputcsv($file, []);
            fputcsv($file, ['Employee Name', $payslip->employee->name]);
            fputcsv($file, ['PPS Number', $payslip->employee->pps_number]);
            fputcsv($file, ['Department', $payslip->employee->department]);
            fputcsv($file, ['Period Start', $payslip->period_start->format('d/m/Y')]);
            fputcsv($file, ['Period End', $payslip->period_end->format('d/m/Y')]);
            fputcsv($file, []);
            fputcsv($file, ['Payment Item', 'Amount (EUR)']);
            fputcsv($file, ['Gross Pay', number_format($payslip->gross_pay, 2)]);
            fputcsv($file, ['PAYE Tax', number_format($payslip->paye, 2)]);
            fputcsv($file, ['USC Charge', number_format($payslip->usc, 2)]);
            fputcsv($file, ['PRSI (Employee)', number_format($payslip->prsi, 2)]);
            fputcsv($file, ['PRSI (Employer)', number_format($payslip->employer_prsi, 2)]);
            fputcsv($file, ['Net Take-home Pay', number_format($payslip->net_pay, 2)]);
            fputcsv($file, []);
            fputcsv($file, ['YTD Accumulations']);
            fputcsv($file, ['Item', 'Prev YTD', 'Current Period', 'Cumulative YTD']);
            fputcsv($file, ['Gross Wages', number_format($prevAccumulated['gross_pay'], 2), number_format($payslip->gross_pay, 2), number_format($accumulated['gross_pay'], 2)]);
            fputcsv($file, ['PAYE Tax', number_format($prevAccumulated['paye'], 2), number_format($payslip->paye, 2), number_format($accumulated['paye'], 2)]);
            fputcsv($file, ['USC Charge', number_format($prevAccumulated['usc'], 2), number_format($payslip->usc, 2), number_format($accumulated['usc'], 2)]);
            fputcsv($file, ['PRSI Employee', number_format($prevAccumulated['prsi'], 2), number_format($payslip->prsi, 2), number_format($accumulated['prsi'], 2)]);
            fputcsv($file, ['Net Take-home', number_format($prevAccumulated['net_pay'], 2), number_format($payslip->net_pay, 2), number_format($accumulated['net_pay'], 2)]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export a single payslip as a JPEG image.
     */
    public function exportPayslipJpeg(Payslip $payslip, Request $request)
    {
        $payslip->load('employee');

        // Fetch Accumulated Previous Payslips YTD (excluding current)
        $year = $payslip->period_end->format('Y');
        $prevAccumulatedQuery = Payslip::where('employee_id', $payslip->employee_id)
            ->whereYear('period_end', $year)
            ->where('period_end', '<', $payslip->period_start)
            ->get();

        $prevAccumulated = [
            'gross_pay' => $prevAccumulatedQuery->sum('gross_pay'),
            'paye' => $prevAccumulatedQuery->sum('paye'),
            'usc' => $prevAccumulatedQuery->sum('usc'),
            'prsi' => $prevAccumulatedQuery->sum('prsi'),
            'employer_prsi' => $prevAccumulatedQuery->sum('employer_prsi'),
            'net_pay' => $prevAccumulatedQuery->sum('net_pay'),
            'has_records' => $prevAccumulatedQuery->isNotEmpty(),
        ];

        // Fetch Accumulated Year-to-Date up to current payslip
        $accumulatedQuery = Payslip::where('employee_id', $payslip->employee_id)
            ->whereYear('period_end', $year)
            ->where('period_end', '<=', $payslip->period_end)
            ->get();

        $accumulated = [
            'gross_pay' => $accumulatedQuery->sum('gross_pay'),
            'paye' => $accumulatedQuery->sum('paye'),
            'usc' => $accumulatedQuery->sum('usc'),
            'prsi' => $accumulatedQuery->sum('prsi'),
            'employer_prsi' => $accumulatedQuery->sum('employer_prsi'),
            'net_pay' => $accumulatedQuery->sum('net_pay'),
        ];
        
        $width = 800;
        $height = 700;
        $image = imagecreatetruecolor($width, $height);
        
        $bg = imagecolorallocate($image, 11, 19, 41);
        $cardBg = imagecolorallocate($image, 15, 23, 42);
        $textMain = imagecolorallocate($image, 255, 255, 255);
        $textMuted = imagecolorallocate($image, 148, 163, 184);
        $emerald = imagecolorallocate($image, 16, 185, 129);
        $rose = imagecolorallocate($image, 239, 68, 68);
        
        imagefill($image, 0, 0, $bg);
        imagefilledrectangle($image, 40, 40, $width - 40, $height - 40, $cardBg);
        
        imagestring($image, 5, 80, 70, "PAYSLIP STATEMENT - iTax", $emerald);
        imageline($image, 80, 100, $width - 80, 100, $textMuted);
        
        imagestring($image, 4, 80, 120, "Employee Name: " . $payslip->employee->name, $textMain);
        imagestring($image, 4, 80, 145, "PPS Number:    " . $payslip->employee->pps_number, $textMain);
        imagestring($image, 4, 80, 170, "Department:    " . $payslip->employee->department, $textMain);
        imagestring($image, 4, 80, 195, "Period:        " . $payslip->period_start->format('d/m/Y') . " to " . $payslip->period_end->format('d/m/Y'), $textMain);
        
        imageline($image, 80, 230, $width - 80, 230, $textMuted);

        // 1. Prev YTD Column
        imagestring($image, 4, 80, 250, "1. Prev Accumulated", $textMuted);
        imageline($image, 80, 270, 260, 270, $textMuted);
        imagestring($image, 3, 80, 285, "Gross: EUR " . number_format($prevAccumulated['gross_pay'], 2), $textMain);
        imagestring($image, 3, 80, 305, "PAYE:  EUR " . number_format($prevAccumulated['paye'], 2), $rose);
        imagestring($image, 3, 80, 325, "USC:   EUR " . number_format($prevAccumulated['usc'], 2), $rose);
        imagestring($image, 3, 80, 345, "PRSI:  EUR " . number_format($prevAccumulated['prsi'], 2), $rose);
        imagestring($image, 4, 80, 375, "Net:   EUR " . number_format($prevAccumulated['net_pay'], 2), $emerald);

        // 2. Current Period Column
        imagestring($image, 4, 310, 250, "2. Current Period", $emerald);
        imageline($image, 310, 270, 490, 270, $emerald);
        imagestring($image, 3, 310, 285, "Gross: EUR " . number_format($payslip->gross_pay, 2), $textMain);
        imagestring($image, 3, 310, 305, "PAYE:  EUR " . number_format($payslip->paye, 2), $rose);
        imagestring($image, 3, 310, 325, "USC:   EUR " . number_format($payslip->usc, 2), $rose);
        imagestring($image, 3, 310, 345, "PRSI:  EUR " . number_format($payslip->prsi, 2), $rose);
        imagestring($image, 4, 310, 375, "Net:   EUR " . number_format($payslip->net_pay, 2), $emerald);

        // 3. YTD Column
        imagestring($image, 4, 540, 250, "3. YTD Accumulated", $textMuted);
        imageline($image, 540, 270, 720, 270, $textMuted);
        imagestring($image, 3, 540, 285, "Gross: EUR " . number_format($accumulated['gross_pay'], 2), $textMain);
        imagestring($image, 3, 540, 305, "PAYE:  EUR " . number_format($accumulated['paye'], 2), $rose);
        imagestring($image, 3, 540, 325, "USC:   EUR " . number_format($accumulated['usc'], 2), $rose);
        imagestring($image, 3, 540, 345, "PRSI:  EUR " . number_format($accumulated['prsi'], 2), $rose);
        imagestring($image, 4, 540, 375, "Net:   EUR " . number_format($accumulated['net_pay'], 2), $emerald);
        
        imageline($image, 80, 420, $width - 80, 420, $textMuted);

        // Detailed Cost Breakdown
        imagestring($image, 4, 80, 440, "Earnings & Cost Breakdown", $textMuted);
        imageline($image, 80, 460, 360, 460, $textMuted);
        imagestring($image, 3, 80, 475, "Basic Pay:", $textMain);
        imagestring($image, 3, 220, 475, "EUR " . number_format($payslip->gross_pay - $payslip->bonus, 2), $textMain);
        if ($payslip->bonus > 0) {
            imagestring($image, 3, 80, 495, "Bonus / Allowance:", $textMain);
            imagestring($image, 3, 220, 495, "EUR " . number_format($payslip->bonus, 2), $textMain);
        }
        imagestring($image, 3, 80, 520, "Employer PRSI (ER):", $textMain);
        imagestring($image, 3, 220, 520, "EUR " . number_format($payslip->employer_prsi, 2), $textMuted);
        imagestring($image, 4, 80, 545, "Total Employer Cost:", $emerald);
        imagestring($image, 4, 250, 545, "EUR " . number_format($payslip->gross_pay + $payslip->employer_prsi, 2), $emerald);

        imagestring($image, 4, 440, 440, "Total Period Deductions", $textMuted);
        imageline($image, 440, 460, 720, 460, $textMuted);
        imagestring($image, 3, 440, 475, "PAYE Tax:", $textMain);
        imagestring($image, 3, 600, 475, "EUR " . number_format($payslip->paye, 2), $rose);
        imagestring($image, 3, 440, 495, "USC Charge:", $textMain);
        imagestring($image, 3, 600, 495, "EUR " . number_format($payslip->usc, 2), $rose);
        imagestring($image, 3, 440, 515, "PRSI EE:", $textMain);
        imagestring($image, 3, 600, 515, "EUR " . number_format($payslip->prsi, 2), $rose);
        imagestring($image, 4, 440, 545, "Total Deductions:", $rose);
        imagestring($image, 4, 600, 545, "EUR " . number_format($payslip->paye + $payslip->usc + $payslip->prsi, 2), $rose);
        
        imageline($image, 80, 590, $width - 80, 590, $textMuted);
        
        imagestring($image, 5, 80, 610, "NET TAKE-HOME PAY:", $emerald);
        imagestring($image, 5, 250, 610, "EUR " . number_format($payslip->net_pay, 2), $emerald);
        
        imagestring($image, 2, 80, 650, "Generated by iTax Payroll Management System on " . now()->format('d/m/Y H:i'), $textMuted);
        
        $filename = 'Payslip_' . str_replace(' ', '_', $payslip->employee->name) . '_' . $payslip->period_start->format('Ymd') . '.jpg';
        if ($request->has('download')) {
            header('Content-Type: image/jpeg');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        } else {
            header('Content-Type: image/jpeg');
        }
        imagejpeg($image);
        imagedestroy($image);
        exit;
    }

    /**
     * Export full employee report as an XLSX (CSV) document.
     */
    public function exportEmployeeReportXlsx(Employee $employee)
    {
        $payslips = $employee->payslips()->orderBy('period_end', 'desc')->get();

        // Calculate YTD accumulated totals
        $currentYear = now()->year;
        $ytdPayslips = $employee->payslips()
            ->whereYear('period_end', $currentYear)
            ->get();

        $accumulated = [
            'gross_pay' => $ytdPayslips->sum('gross_pay'),
            'paye' => $ytdPayslips->sum('paye'),
            'usc' => $ytdPayslips->sum('usc'),
            'prsi' => $ytdPayslips->sum('prsi'),
            'employer_prsi' => $ytdPayslips->sum('employer_prsi'),
            'net_pay' => $ytdPayslips->sum('net_pay'),
        ];

        $filename = 'Employee_Report_' . str_replace(' ', '_', $employee->name) . '_' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($employee, $payslips, $accumulated) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['EMPLOYEE PAYROLL SUMMARY REPORT - iTax']);
            fputcsv($file, []);
            fputcsv($file, ['Employee Name', $employee->name]);
            fputcsv($file, ['PPS Number', $employee->pps_number]);
            fputcsv($file, ['Department', $employee->department]);
            fputcsv($file, []);
            fputcsv($file, ['Period Start', 'Period End', 'Gross Pay', 'PAYE', 'USC', 'PRSI (EE)', 'PRSI (ER)', 'Net Pay']);
            foreach ($payslips as $ps) {
                fputcsv($file, [
                    $ps->period_start->format('d/m/Y'),
                    $ps->period_end->format('d/m/Y'),
                    number_format($ps->gross_pay, 2),
                    number_format($ps->paye, 2),
                    number_format($ps->usc, 2),
                    number_format($ps->prsi, 2),
                    number_format($ps->employer_prsi, 2),
                    number_format($ps->net_pay, 2),
                ]);
            }
            fputcsv($file, []);
            fputcsv($file, ['YTD ACCUMULATED TOTALS']);
            fputcsv($file, ['YTD Gross', 'YTD PAYE', 'YTD USC', 'YTD PRSI (EE)', 'YTD PRSI (ER)', 'YTD Net']);
            fputcsv($file, [
                number_format($accumulated['gross_pay'], 2),
                number_format($accumulated['paye'], 2),
                number_format($accumulated['usc'], 2),
                number_format($accumulated['prsi'], 2),
                number_format($accumulated['employer_prsi'], 2),
                number_format($accumulated['net_pay'], 2),
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export full employee report as a JPEG image.
     */
    public function exportEmployeeReportJpeg(Employee $employee, Request $request)
    {
        $payslips = $employee->payslips()->orderBy('period_end', 'desc')->take(10)->get();

        // Calculate YTD accumulated totals
        $currentYear = now()->year;
        $ytdPayslips = $employee->payslips()
            ->whereYear('period_end', $currentYear)
            ->get();

        $accumulated = [
            'gross_pay' => $ytdPayslips->sum('gross_pay'),
            'paye' => $ytdPayslips->sum('paye'),
            'usc' => $ytdPayslips->sum('usc'),
            'prsi' => $ytdPayslips->sum('prsi'),
            'employer_prsi' => $ytdPayslips->sum('employer_prsi'),
            'net_pay' => $ytdPayslips->sum('net_pay'),
        ];
        
        $width = 800;
        $height = 700;
        $image = imagecreatetruecolor($width, $height);
        
        $bg = imagecolorallocate($image, 11, 19, 41);
        $cardBg = imagecolorallocate($image, 15, 23, 42);
        $textMain = imagecolorallocate($image, 255, 255, 255);
        $textMuted = imagecolorallocate($image, 148, 163, 184);
        $emerald = imagecolorallocate($image, 16, 185, 129);
        $rose = imagecolorallocate($image, 239, 68, 68);
        
        imagefill($image, 0, 0, $bg);
        imagefilledrectangle($image, 40, 40, $width - 40, $height - 40, $cardBg);
        
        imagestring($image, 5, 60, 60, "EMPLOYEE PAYROLL YTD REPORT - iTax", $emerald);
        imageline($image, 60, 90, $width - 60, 90, $textMuted);
        
        imagestring($image, 4, 60, 110, "Employee Name: " . $employee->name, $textMain);
        imagestring($image, 4, 60, 130, "PPS Number:    " . $employee->pps_number, $textMain);
        imagestring($image, 4, 60, 150, "Department:    " . $employee->department, $textMain);
        
        imageline($image, 60, 180, $width - 60, 180, $textMuted);
        
        imagestring($image, 4, 60, 200, "Period End", $textMuted);
        imagestring($image, 4, 200, 200, "Gross Pay", $textMuted);
        imagestring($image, 4, 320, 200, "PAYE", $textMuted);
        imagestring($image, 4, 420, 200, "USC", $textMuted);
        imagestring($image, 4, 520, 200, "PRSI (EE)", $textMuted);
        imagestring($image, 4, 640, 200, "Net Pay", $emerald);
        imageline($image, 60, 225, $width - 60, 225, $textMuted);
        
        $y = 240;
        foreach ($payslips as $ps) {
            imagestring($image, 4, 60, $y, $ps->period_end->format('d/m/Y'), $textMain);
            imagestring($image, 4, 200, $y, "EUR " . number_format($ps->gross_pay, 2), $textMain);
            imagestring($image, 4, 320, $y, "EUR " . number_format($ps->paye, 2), $rose);
            imagestring($image, 4, 420, $y, "EUR " . number_format($ps->usc, 2), $rose);
            imagestring($image, 4, 520, $y, "EUR " . number_format($ps->prsi, 2), $rose);
            imagestring($image, 4, 640, $y, "EUR " . number_format($ps->net_pay, 2), $emerald);
            $y += 30;
        }
        
        imageline($image, 60, $y + 10, $width - 60, $y + 10, $textMuted);

        imageline($image, 60, $y + 20, $width - 60, $y + 20, $emerald);
        imagestring($image, 4, 60, $y + 35, "YTD TOTALS:", $emerald);
        imagestring($image, 3, 180, $y + 35, "Gross: " . number_format($accumulated['gross_pay'], 2), $textMain);
        imagestring($image, 3, 340, $y + 35, "PAYE: " . number_format($accumulated['paye'], 2), $rose);
        imagestring($image, 3, 460, $y + 35, "USC: " . number_format($accumulated['usc'], 2), $rose);
        imagestring($image, 3, 565, $y + 35, "PRSI: " . number_format($accumulated['prsi'], 2), $rose);
        imagestring($image, 4, 670, $y + 35, "Net: " . number_format($accumulated['net_pay'], 2), $emerald);

        imageline($image, 60, $y + 60, $width - 60, $y + 60, $textMuted);
        
        imagestring($image, 2, 60, $height - 40, "Generated by iTax Payroll Management System on " . now()->format('d/m/Y H:i'), $textMuted);
        
        $filename = 'Employee_Report_' . str_replace(' ', '_', $employee->name) . '_' . now()->format('Ymd') . '.jpg';
        if ($request->has('download')) {
            header('Content-Type: image/jpeg');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        } else {
            header('Content-Type: image/jpeg');
        }
        imagejpeg($image);
        imagedestroy($image);
        exit;
    }
}
