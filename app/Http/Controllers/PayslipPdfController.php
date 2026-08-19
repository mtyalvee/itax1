<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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

        return $pdf->download($filename);
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

        return $pdf->download($filename);
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payslip');

        // Gridlines visible
        $sheet->setShowGridlines(true);

        // Styling helper variables
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '1C1B1A'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFEDE6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ];

        $titleStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '059669'], 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        // Write title
        $sheet->mergeCells('B2:H2');
        $sheet->setCellValue('B2', 'PAYSLIP STATEMENT - iTax');
        $sheet->getStyle('B2:H2')->applyFromArray($titleStyle);

        // Write Employee Info Section
        $sheet->mergeCells('B4:H4');
        $sheet->setCellValue('B4', ' Employee Profile');
        $sheet->getStyle('B4:H4')->applyFromArray($headerStyle);

        $sheet->setCellValue('B5', 'Employee Name:');
        $sheet->setCellValue('C5', $payslip->employee->name);
        $sheet->setCellValue('F5', 'PPS Number:');
        $sheet->setCellValue('G5', $payslip->employee->pps_number);

        $sheet->setCellValue('B6', 'Department:');
        $sheet->setCellValue('C6', $payslip->employee->department);
        $sheet->setCellValue('F6', 'Pay Period:');
        $sheet->setCellValue('G6', $payslip->period_start->format('d-m-Y') . ' to ' . $payslip->period_end->format('d-m-Y'));

        // Format employee values bold
        $sheet->getStyle('C5')->getFont()->setBold(true);
        $sheet->getStyle('G5')->getFont()->setBold(true);
        $sheet->getStyle('C6')->getFont()->setBold(true);
        $sheet->getStyle('G6')->getFont()->setBold(true);

        // Write Comparison columns
        $sheet->mergeCells('B8:H8');
        $sheet->setCellValue('B8', ' Payslip History & Accumulations Ledger (€)');
        $sheet->getStyle('B8:H8')->applyFromArray($headerStyle);

        $sheet->setCellValue('B9', 'Item');
        $sheet->mergeCells('C9:D9');
        $sheet->setCellValue('C9', '1. Prev Accumulated (€)');
        $sheet->mergeCells('E9:F9');
        $sheet->setCellValue('E9', '2. Current Period (€)');
        $sheet->mergeCells('G9:H9');
        $sheet->setCellValue('G9', '3. YTD Accumulated (€)');
        $sheet->getStyle('B9:H9')->getFont()->setBold(true);
        $sheet->getStyle('C9:H9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $comparisonData = [
            ['Gross Wages', $prevAccumulated['gross_pay'], $payslip->gross_pay, $accumulated['gross_pay']],
            ['PAYE Tax', $prevAccumulated['paye'], $payslip->paye, $accumulated['paye']],
            ['USC Charge', $prevAccumulated['usc'], $payslip->usc, $accumulated['usc']],
            ['PRSI Employee', $prevAccumulated['prsi'], $payslip->prsi, $accumulated['prsi']],
            ['Net Take-home', $prevAccumulated['net_pay'], $payslip->net_pay, $accumulated['net_pay']],
        ];

        $row = 10;
        foreach ($comparisonData as $dataRow) {
            $sheet->setCellValue('B' . $row, $dataRow[0]);
            
            $sheet->mergeCells('C' . $row . ':D' . $row);
            $sheet->setCellValue('C' . $row, $dataRow[1]);
            
            $sheet->mergeCells('E' . $row . ':F' . $row);
            $sheet->setCellValue('E' . $row, $dataRow[2]);
            
            $sheet->mergeCells('G' . $row . ':H' . $row);
            $sheet->setCellValue('G' . $row, $dataRow[3]);

            // Number formatting
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            if ($dataRow[0] === 'Net Take-home') {
                $sheet->getStyle('B' . $row . ':H' . $row)->getFont()->setBold(true);
                $sheet->getStyle('C' . $row)->getFont()->getColor()->setRGB('059669');
                $sheet->getStyle('E' . $row)->getFont()->getColor()->setRGB('059669');
                $sheet->getStyle('G' . $row)->getFont()->getColor()->setRGB('059669');
            }

            $row++;
        }

        // Detailed Cost Breakdown
        $sheet->mergeCells('B16:H16');
        $sheet->setCellValue('B16', ' Detailed Cost & Deductions Breakdown (€)');
        $sheet->getStyle('B16:H16')->applyFromArray($headerStyle);

        $sheet->mergeCells('B17:D17');
        $sheet->setCellValue('B17', 'Earnings & Cost');
        $sheet->mergeCells('E17:H17');
        $sheet->setCellValue('E17', 'Period Deductions');
        $sheet->getStyle('B17:H17')->getFont()->setBold(true);
        $sheet->getStyle('E17')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->setCellValue('B18', 'Basic Pay:');
        $sheet->setCellValue('D18', $payslip->gross_pay - $payslip->bonus);
        $sheet->setCellValue('E18', 'PAYE Tax:');
        $sheet->setCellValue('H18', $payslip->paye);

        $sheet->setCellValue('B19', 'Bonus / Allowance:');
        $sheet->setCellValue('D19', $payslip->bonus);
        $sheet->setCellValue('E19', 'USC Charge:');
        $sheet->setCellValue('H19', $payslip->usc);

        $sheet->setCellValue('B20', 'Employer PRSI (ER):');
        $sheet->setCellValue('D20', $payslip->employer_prsi);
        $sheet->setCellValue('E20', 'PRSI EE:');
        $sheet->setCellValue('H20', $payslip->prsi);

        $sheet->setCellValue('B21', 'Total Employer Cost:');
        $sheet->setCellValue('D21', $payslip->gross_pay + $payslip->employer_prsi);
        $sheet->getStyle('B21:D21')->getFont()->setBold(true);
        $sheet->getStyle('D21')->getFont()->getColor()->setRGB('059669');

        $sheet->setCellValue('E21', 'Total Deductions:');
        $sheet->setCellValue('H21', $payslip->paye + $payslip->usc + $payslip->prsi);
        $sheet->getStyle('E21:H21')->getFont()->setBold(true);
        $sheet->getStyle('H21')->getFont()->getColor()->setRGB('DC2626');

        // Formats for breakdown values
        foreach ([18, 19, 20, 21] as $r) {
            $sheet->getStyle('D' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('H' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('D' . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('H' . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // Net Pay box
        $sheet->mergeCells('B23:D23');
        $sheet->setCellValue('B23', 'NET TAKE-HOME PAY (€):');
        $sheet->getStyle('B23')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('059669');

        $sheet->mergeCells('E23:H23');
        $sheet->setCellValue('E23', $payslip->net_pay);
        $sheet->getStyle('E23')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('059669');
        $sheet->getStyle('E23')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('E23')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Footer note
        $sheet->mergeCells('B25:H25');
        $sheet->setCellValue('B25', ' Generated by iTax Payroll Management System on ' . now()->format('d-m-Y H:i'));
        $sheet->getStyle('B25')->getFont()->setItalic(true)->setSize(8)->getColor()->setRGB('706F6C');

        // Auto-fit columns
        foreach (range('B', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add page frame (borders around B2:H25)
        $outerBorder = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2DED4'],
                ],
            ],
        ];
        $sheet->getStyle('B2:H25')->applyFromArray($outerBorder);

        // Thin borders on inner structures
        $thinBorderBottom = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2DED4'],
                ],
            ],
        ];
        $sheet->getStyle('B5:H5')->applyFromArray($thinBorderBottom);
        $sheet->getStyle('B6:H6')->applyFromArray($thinBorderBottom);
        $sheet->getStyle('B9:H9')->applyFromArray($thinBorderBottom);
        $sheet->getStyle('B10:H10')->applyFromArray($thinBorderBottom);
        $sheet->getStyle('B11:H11')->applyFromArray($thinBorderBottom);
        $sheet->getStyle('B12:H12')->applyFromArray($thinBorderBottom);
        $sheet->getStyle('B13:H13')->applyFromArray($thinBorderBottom);
        $sheet->getStyle('B18:H18')->applyFromArray($thinBorderBottom);
        $sheet->getStyle('B19:H19')->applyFromArray($thinBorderBottom);
        $sheet->getStyle('B20:H20')->applyFromArray($thinBorderBottom);
        $sheet->getStyle('B21:H21')->applyFromArray($thinBorderBottom);

        $filename = 'Payslip_' . str_replace(' ', '_', $payslip->employee->name) . '_' . $payslip->period_start->format('Ymd') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
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
        
        $bg = imagecolorallocate($image, 247, 245, 240); // Soft off-white
        $cardBg = imagecolorallocate($image, 255, 255, 255); // Pure white
        $frameColor = imagecolorallocate($image, 226, 222, 212); // Soft border color
        $textMain = imagecolorallocate($image, 46, 44, 41); // Soft dark text
        $textMuted = imagecolorallocate($image, 112, 111, 108); // Soft muted text
        $emerald = imagecolorallocate($image, 5, 150, 105); // Premium emerald
        $rose = imagecolorallocate($image, 220, 38, 38); // Premium red/rose
        
        imagefill($image, 0, 0, $bg);
        $this->drawRoundedRectangle($image, 40, 40, $width - 40, $height - 40, 16, $cardBg, true);
        $this->drawRoundedRectangle($image, 40, 40, $width - 40, $height - 40, 16, $frameColor, false);
        
        imagestring($image, 5, 80, 70, "PAYSLIP STATEMENT - iTax", $emerald);
        imageline($image, 80, 100, $width - 80, 100, $textMuted);
        
        imagestring($image, 4, 80, 120, "Employee Name: " . $payslip->employee->name, $textMain);
        imagestring($image, 4, 80, 145, "PPS Number:    " . $payslip->employee->pps_number, $textMain);
        imagestring($image, 4, 80, 170, "Department:    " . $payslip->employee->department, $textMain);
        imagestring($image, 4, 80, 195, "Period:        " . $payslip->period_start->format('d/m/Y') . " to " . $payslip->period_end->format('d/m/Y'), $textMain);
        
        imageline($image, 80, 230, $width - 80, 230, $textMuted);

        // 1. Prev YTD Column
        imagestring($image, 4, 80, 250, "1. Prev Accum (  )", $textMuted);
        $this->drawEuroSymbol($image, 202, 250, $textMuted);
        imageline($image, 80, 270, 260, 270, $textMuted);
        imagestring($image, 3, 80, 285, "Gross: " . number_format($prevAccumulated['gross_pay'], 2), $textMain);
        imagestring($image, 3, 80, 305, "PAYE:  " . number_format($prevAccumulated['paye'], 2), $rose);
        imagestring($image, 3, 80, 325, "USC:   " . number_format($prevAccumulated['usc'], 2), $rose);
        imagestring($image, 3, 80, 345, "PRSI:  " . number_format($prevAccumulated['prsi'], 2), $rose);
        imagestring($image, 4, 80, 375, "Net:   " . number_format($prevAccumulated['net_pay'], 2), $emerald);

        // 2. Current Period Column
        imagestring($image, 4, 310, 250, "2. Current (  )", $emerald);
        $this->drawEuroSymbol($image, 408, 250, $emerald);
        imageline($image, 310, 270, 490, 270, $emerald);
        imagestring($image, 3, 310, 285, "Gross: " . number_format($payslip->gross_pay, 2), $textMain);
        imagestring($image, 3, 310, 305, "PAYE:  " . number_format($payslip->paye, 2), $rose);
        imagestring($image, 3, 310, 325, "USC:   " . number_format($payslip->usc, 2), $rose);
        imagestring($image, 3, 310, 345, "PRSI:  " . number_format($payslip->prsi, 2), $rose);
        imagestring($image, 4, 310, 375, "Net:   " . number_format($payslip->net_pay, 2), $emerald);

        // 3. YTD Column
        imagestring($image, 4, 540, 250, "3. YTD Accum (  )", $textMuted);
        $this->drawEuroSymbol($image, 654, 250, $textMuted);
        imageline($image, 540, 270, 720, 270, $textMuted);
        imagestring($image, 3, 540, 285, "Gross: " . number_format($accumulated['gross_pay'], 2), $textMain);
        imagestring($image, 3, 540, 305, "PAYE:  " . number_format($accumulated['paye'], 2), $rose);
        imagestring($image, 3, 540, 325, "USC:   " . number_format($accumulated['usc'], 2), $rose);
        imagestring($image, 3, 540, 345, "PRSI:  " . number_format($accumulated['prsi'], 2), $rose);
        imagestring($image, 4, 540, 375, "Net:   " . number_format($accumulated['net_pay'], 2), $emerald);
        
        imageline($image, 80, 420, $width - 80, 420, $textMuted);

        // Detailed Cost Breakdown
        imagestring($image, 4, 80, 440, "Earnings & Cost Breakdown (  )", $textMuted);
        $this->drawEuroSymbol($image, 298, 440, $textMuted);
        imageline($image, 80, 460, 360, 460, $textMuted);
        imagestring($image, 3, 80, 475, "Basic Pay:", $textMain);
        imagestring($image, 3, 220, 475, number_format($payslip->gross_pay - $payslip->bonus, 2), $textMain);
        if ($payslip->bonus > 0) {
            imagestring($image, 3, 80, 495, "Bonus / Allowance:", $textMain);
            imagestring($image, 3, 220, 495, number_format($payslip->bonus, 2), $textMain);
        }
        imagestring($image, 3, 80, 520, "Employer PRSI (ER):", $textMain);
        imagestring($image, 3, 220, 520, number_format($payslip->employer_prsi, 2), $textMuted);
        imagestring($image, 4, 80, 545, "Total Employer Cost:", $emerald);
        imagestring($image, 4, 250, 545, number_format($payslip->gross_pay + $payslip->employer_prsi, 2), $emerald);

        imagestring($image, 4, 440, 440, "Total Deductions (  )", $textMuted);
        $this->drawEuroSymbol($image, 586, 440, $textMuted);
        imageline($image, 440, 460, 720, 460, $textMuted);
        imagestring($image, 3, 440, 475, "PAYE Tax:", $textMain);
        imagestring($image, 3, 600, 475, number_format($payslip->paye, 2), $rose);
        imagestring($image, 3, 440, 495, "USC Charge:", $textMain);
        imagestring($image, 3, 600, 495, number_format($payslip->usc, 2), $rose);
        imagestring($image, 3, 440, 515, "PRSI EE:", $textMain);
        imagestring($image, 3, 600, 515, number_format($payslip->prsi, 2), $rose);
        imagestring($image, 4, 440, 545, "Total Deductions:", $rose);
        imagestring($image, 4, 600, 545, number_format($payslip->paye + $payslip->usc + $payslip->prsi, 2), $rose);
        
        imageline($image, 80, 590, $width - 80, 590, $textMuted);
        
        imagestring($image, 5, 80, 610, "NET TAKE-HOME PAY (  ):", $emerald);
        $this->drawEuroSymbol($image, 253, 610, $emerald);
        imagestring($image, 5, 305, 610, number_format($payslip->net_pay, 2), $emerald);
        
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('YTD Summary');

        // Gridlines visible
        $sheet->setShowGridlines(true);

        // Styling helper variables
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '1C1B1A'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFEDE6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ];

        $titleStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '059669'], 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        // Title
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'EMPLOYEE PAYROLL SUMMARY REPORT - iTax');
        $sheet->getStyle('A2:H2')->applyFromArray($titleStyle);

        // Employee Info Section
        $sheet->mergeCells('A4:H4');
        $sheet->setCellValue('A4', ' Employee Profile');
        $sheet->getStyle('A4:H4')->applyFromArray($headerStyle);

        $sheet->setCellValue('A5', 'Employee Name:');
        $sheet->setCellValue('B5', $employee->name);
        $sheet->setCellValue('E5', 'PPS Number:');
        $sheet->setCellValue('F5', $employee->pps_number);

        $sheet->setCellValue('A6', 'Department:');
        $sheet->setCellValue('B6', $employee->department);

        $sheet->getStyle('B5')->getFont()->setBold(true);
        $sheet->getStyle('F5')->getFont()->setBold(true);
        $sheet->getStyle('B6')->getFont()->setBold(true);

        // Table Header
        $sheet->setCellValue('A8', 'Period Start');
        $sheet->setCellValue('B8', 'Period Ended');
        $sheet->setCellValue('C8', 'Gross Pay (€)');
        $sheet->setCellValue('D8', 'PAYE (€)');
        $sheet->setCellValue('E8', 'USC (€)');
        $sheet->setCellValue('F8', 'PRSI (EE) (€)');
        $sheet->setCellValue('G8', 'PRSI (ER) (€)');
        $sheet->setCellValue('H8', 'Net Pay (€)');
        
        $sheet->getStyle('A8:H8')->applyFromArray($headerStyle);
        $sheet->getStyle('C8:H8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $row = 9;
        foreach ($payslips as $ps) {
            $sheet->setCellValue('A' . $row, $ps->period_start->format('d-m-Y'));
            $sheet->setCellValue('B' . $row, $ps->period_end->format('d-m-Y'));
            $sheet->setCellValue('C' . $row, $ps->gross_pay);
            $sheet->setCellValue('D' . $row, $ps->paye);
            $sheet->setCellValue('E' . $row, $ps->usc);
            $sheet->setCellValue('F' . $row, $ps->prsi);
            $sheet->setCellValue('G' . $row, $ps->employer_prsi);
            $sheet->setCellValue('H' . $row, $ps->net_pay);

            // Alignments & Number formats
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            $row++;
        }

        $row += 1;

        // Totals Section
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('A' . $row, 'YTD ACCUMULATED TOTALS');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('059669');

        $row += 1;
        $sheet->setCellValue('A' . $row, 'YTD Gross (€)');
        $sheet->setCellValue('B' . $row, 'YTD PAYE (€)');
        $sheet->setCellValue('C' . $row, 'YTD USC (€)');
        $sheet->setCellValue('D' . $row, 'YTD PRSI (EE) (€)');
        $sheet->setCellValue('E' . $row, 'YTD PRSI (ER) (€)');
        $sheet->setCellValue('F' . $row, 'YTD Net (€)');
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($headerStyle);
        $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $row += 1;
        $sheet->setCellValue('A' . $row, $accumulated['gross_pay']);
        $sheet->setCellValue('B' . $row, $accumulated['paye']);
        $sheet->setCellValue('C' . $row, $accumulated['usc']);
        $sheet->setCellValue('D' . $row, $accumulated['prsi']);
        $sheet->setCellValue('E' . $row, $accumulated['employer_prsi']);
        $sheet->setCellValue('F' . $row, $accumulated['net_pay']);

        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('059669');
        $sheet->getStyle('F' . $row)->getFont()->getColor()->setRGB('059669');
        $sheet->getStyle('B' . $row)->getFont()->getColor()->setRGB('DC2626');
        $sheet->getStyle('C' . $row)->getFont()->getColor()->setRGB('DC2626');
        $sheet->getStyle('D' . $row)->getFont()->getColor()->setRGB('DC2626');

        $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        $row += 2;

        // Footer note
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('A' . $row, ' Generated by iTax Payroll Management System on ' . now()->format('d-m-Y H:i'));
        $sheet->getStyle('A' . $row)->getFont()->setItalic(true)->setSize(8)->getColor()->setRGB('706F6C');

        // Auto-fit columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add page frame (borders around A2:H$row)
        $outerBorder = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2DED4'],
                ],
            ],
        ];
        $sheet->getStyle('A2:H' . $row)->applyFromArray($outerBorder);

        // Thin borders on inner structures
        $thinBorderBottom = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2DED4'],
                ],
            ],
        ];
        $sheet->getStyle('A5:H5')->applyFromArray($thinBorderBottom);
        $sheet->getStyle('A6:H6')->applyFromArray($thinBorderBottom);
        $sheet->getStyle('A8:H8')->applyFromArray($thinBorderBottom);

        for ($i = 9; $i < $row - 4; $i++) {
            $sheet->getStyle('A' . $i . ':H' . $i)->applyFromArray($thinBorderBottom);
        }
        $sheet->getStyle('A' . ($row - 2) . ':F' . ($row - 2))->applyFromArray($thinBorderBottom); // YTD values bottom border

        $filename = 'Employee_Report_' . str_replace(' ', '_', $employee->name) . '_' . now()->format('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
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
        
        $bg = imagecolorallocate($image, 247, 245, 240); // Soft off-white
        $cardBg = imagecolorallocate($image, 255, 255, 255); // Pure white
        $frameColor = imagecolorallocate($image, 226, 222, 212); // Soft border color
        $textMain = imagecolorallocate($image, 46, 44, 41); // Soft dark text
        $textMuted = imagecolorallocate($image, 112, 111, 108); // Soft muted text
        $emerald = imagecolorallocate($image, 5, 150, 105); // Premium emerald
        $rose = imagecolorallocate($image, 220, 38, 38); // Premium red/rose
        
        imagefill($image, 0, 0, $bg);
        $this->drawRoundedRectangle($image, 40, 40, $width - 40, $height - 40, 16, $cardBg, true);
        $this->drawRoundedRectangle($image, 40, 40, $width - 40, $height - 40, 16, $frameColor, false);
        
        imagestring($image, 5, 60, 60, "EMPLOYEE PAYROLL YTD REPORT - iTax", $emerald);
        imageline($image, 60, 90, $width - 60, 90, $textMuted);
        
        imagestring($image, 4, 60, 110, "Employee Name: " . $employee->name, $textMain);
        imagestring($image, 4, 60, 130, "PPS Number:    " . $employee->pps_number, $textMain);
        imagestring($image, 4, 60, 150, "Department:    " . $employee->department, $textMain);
        
        imageline($image, 60, 180, $width - 60, 180, $textMuted);
        
        imagestring($image, 4, 60, 200, "Period Ended", $textMuted);
        imagestring($image, 4, 200, 200, "Gross Pay (  )", $textMuted);
        $this->drawEuroSymbol($image, 288, 200, $textMuted);
        imagestring($image, 4, 320, 200, "PAYE (  )", $textMuted);
        $this->drawEuroSymbol($image, 368, 200, $textMuted);
        imagestring($image, 4, 420, 200, "USC (  )", $textMuted);
        $this->drawEuroSymbol($image, 460, 200, $textMuted);
        imagestring($image, 4, 520, 200, "PRSI (EE) (  )", $textMuted);
        $this->drawEuroSymbol($image, 608, 200, $textMuted);
        imagestring($image, 4, 640, 200, "Net Pay (  )", $emerald);
        $this->drawEuroSymbol($image, 712, 200, $emerald);
        imageline($image, 60, 225, $width - 60, 225, $textMuted);
        
        $y = 240;
        foreach ($payslips as $ps) {
            imagestring($image, 4, 60, $y, $ps->period_end->format('d-m-Y'), $textMain);
            imagestring($image, 4, 200, $y, number_format($ps->gross_pay, 2), $textMain);
            imagestring($image, 4, 320, $y, number_format($ps->paye, 2), $rose);
            imagestring($image, 4, 420, $y, number_format($ps->usc, 2), $rose);
            imagestring($image, 4, 520, $y, number_format($ps->prsi, 2), $rose);
            imagestring($image, 4, 640, $y, number_format($ps->net_pay, 2), $emerald);
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
    }

    /**
     * Draw a rounded rectangle (filled or outline) using GD.
     */
    private function drawRoundedRectangle($image, $x1, $y1, $x2, $y2, $radius, $color, $filled = false)
    {
        if ($filled) {
            imagefilledarc($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color, IMG_ARC_PIE);
            imagefilledarc($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color, IMG_ARC_PIE);
            imagefilledarc($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color, IMG_ARC_PIE);
            imagefilledarc($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color, IMG_ARC_PIE);
            
            imagefilledrectangle($image, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
            imagefilledrectangle($image, $x1, $y1 + $radius, $x1 + $radius, $y2 - $radius, $color);
            imagefilledrectangle($image, $x2 - $radius, $y1 + $radius, $x2, $y2 - $radius, $color);
        } else {
            imagearc($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color);
            imagearc($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color);
            imagearc($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color);
            imagearc($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color);
            
            imageline($image, $x1 + $radius, $y1, $x2 - $radius, $y1, $color);
            imageline($image, $x1 + $radius, $y2, $x2 - $radius, $y2, $color);
            imageline($image, $x1, $y1 + $radius, $x1, $y2 - $radius, $color);
            imageline($image, $x2, $y1 + $radius, $x2, $y2 - $radius, $color);
        }
    }

    /**
     * Draw Euro symbol manually in GD.
     */
    private function drawEuroSymbol($image, $x, $y, $color)
    {
        imagearc($image, $x + 6, $y + 8, 9, 10, 100, 260, $color);
        imageline($image, $x + 2, $y + 6, $x + 6, $y + 6, $color);
        imageline($image, $x + 2, $y + 9, $x + 6, $y + 9, $color);
    }
}


