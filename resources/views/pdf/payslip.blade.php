<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $employee->name }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #333; margin: 0; padding: 15px; }
        .header-table { width: 100%; margin-bottom: 20px; border-bottom: 2px solid #10b981; padding-bottom: 10px; }
        .header-logo { font-size: 18px; font-weight: bold; color: #111827; }
        .header-logo span { color: #10b981; }
        .header-title { text-align: right; font-size: 16px; font-weight: bold; color: #374151; }
        
        .row-details { width: 100%; margin-bottom: 20px; }
        .col-details { width: 50%; vertical-align: top; }
        .field { margin-bottom: 6px; }
        .label { font-size: 8px; color: #6b7280; text-transform: uppercase; font-weight: bold; }
        .value { font-size: 10px; font-weight: bold; color: #1f2937; }

        /* The 3 Comparison Columns Table */
        .comparison-table { width: 100%; margin-bottom: 25px; table-layout: fixed; }
        .comparison-col { width: 33.33%; padding: 0 5px; vertical-align: top; }
        .card { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; background-color: #f9fafb; }
        .card-current { border: 1px solid #10b981; background-color: #f0fdf4; }
        .card-title { font-size: 9px; font-weight: bold; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #e5e7eb; color: #374151; }
        .card-current .card-title { color: #047857; border-bottom: 1px solid #a7f3d0; }
        
        .card-row { width: 100%; margin-bottom: 4px; font-family: monospace; font-size: 9px; }
        .card-row-label { color: #6b7280; }
        .card-row-value { text-align: right; float: right; font-weight: bold; color: #1f2937; }
        .card-total { border-top: 1px dashed #ccc; padding-top: 4px; margin-top: 6px; font-weight: bold; color: #10b981; }
        .card-current .card-total { color: #047857; }

        /* Detailed Tables */
        .details-table-wrapper { width: 100%; margin-bottom: 15px; }
        .details-col { width: 50%; vertical-align: top; padding: 0 10px; }
        
        h2 { font-size: 11px; font-weight: bold; margin: 0 0 8px 0; padding-bottom: 4px; border-bottom: 1px solid #d1d5db; text-transform: uppercase; color: #374151; letter-spacing: 0.5px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data-table th { text-align: left; font-size: 8px; text-transform: uppercase; color: #4b5563; padding: 4px 6px; border-bottom: 1px solid #9ca3af; }
        table.data-table th:last-child { text-align: right; }
        table.data-table td { padding: 5px 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; color: #374151; }
        table.data-table td:last-child { text-align: right; font-weight: bold; }
        table.data-table tr.total-row td { border-top: 1px solid #4b5563; border-bottom: none; font-weight: bold; font-size: 10px; color: #111827; }

        .net-pay-box { text-align: center; margin: 15px 0; padding: 10px; border: 2px solid #10b981; border-radius: 6px; background-color: #f0fdf4; }
        .net-pay-box .lbl { font-size: 9px; text-transform: uppercase; color: #047857; font-weight: bold; }
        .net-pay-box .amt { font-size: 20px; font-weight: bold; color: #065f46; margin-top: 2px; }

        .footer { margin-top: 30px; padding-top: 8px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-logo" style="border:none; padding:0;">PAY<span>ROLL</span></td>
            <td class="header-title" style="border:none; padding:0; text-align:right;">PAYSLIP</td>
        </tr>
    </table>

    <table class="row-details" style="border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <td class="col-details" style="border:none; padding:0;">
                <div class="field"><span class="label">Employee Name:</span> <span class="value">{{ $employee->name }}</span></div>
                <div class="field"><span class="label">PPS Number:</span> <span class="value">{{ $employee->pps_number }}</span></div>
                <div class="field"><span class="label">Department / Title:</span> <span class="value">{{ $employee->department }} &mdash; {{ $employee->job_title }}</span></div>
            </td>
            <td class="col-details" style="border:none; padding:0; text-align: right;">
                <div class="field"><span class="label">Pay Period:</span> <span class="value">{{ $payslip->period_start->format('d M Y') }} &ndash; {{ $payslip->period_end->format('d M Y') }}</span></div>
                <div class="field"><span class="label">Payment Status:</span> <span class="value">{{ ucfirst($payslip->status) }}</span></div>
                <div class="field"><span class="label">Employee Email:</span> <span class="value">{{ $employee->email }}</span></div>
            </td>
        </tr>
    </table>

    <!-- The 3 Segment Comparison Panels -->
    <table class="comparison-table">
        <tr>
            <!-- 1. Previous Section -->
            <td class="comparison-col">
                <div class="card">
                    <div class="card-title">1. Prev Accumulated</div>
                    @if($prevAccumulated['has_records'])
                        <div class="card-row"><span class="card-row-label">Gross Wages:</span> <span class="card-row-value">€{{ number_format($prevAccumulated['gross_pay'], 2) }}</span></div>
                        <div class="card-row"><span class="card-row-label">PAYE Tax:</span> <span class="card-row-value">€{{ number_format($prevAccumulated['paye'], 2) }}</span></div>
                        <div class="card-row"><span class="card-row-label">USC Charge:</span> <span class="card-row-value">€{{ number_format($prevAccumulated['usc'], 2) }}</span></div>
                        <div class="card-row"><span class="card-row-label">PRSI Employee:</span> <span class="card-row-value">€{{ number_format($prevAccumulated['prsi'], 2) }}</span></div>
                        <div class="card-row card-total"><span class="card-row-label">Net Pay:</span> <span class="card-row-value">€{{ number_format($prevAccumulated['net_pay'], 2) }}</span></div>
                    @else
                        <div class="card-row"><span class="card-row-label">Gross Wages:</span> <span class="card-row-value">€0.00</span></div>
                        <div class="card-row"><span class="card-row-label">PAYE Tax:</span> <span class="card-row-value">€0.00</span></div>
                        <div class="card-row"><span class="card-row-label">USC Charge:</span> <span class="card-row-value">€0.00</span></div>
                        <div class="card-row"><span class="card-row-label">PRSI Employee:</span> <span class="card-row-value">€0.00</span></div>
                        <div class="card-row card-total"><span class="card-row-label">Net Pay:</span> <span class="card-row-value">€0.00</span></div>
                    @endif
                </div>
            </td>

            <!-- 2. Current Section -->
            <td class="comparison-col">
                <div class="card card-current">
                    <div class="card-title">2. Current Period</div>
                    <div class="card-row"><span class="card-row-label">Gross Wages:</span> <span class="card-row-value">€{{ number_format($payslip->gross_pay, 2) }}</span></div>
                    <div class="card-row"><span class="card-row-label">PAYE Tax:</span> <span class="card-row-value">€{{ number_format($payslip->paye, 2) }}</span></div>
                    <div class="card-row"><span class="card-row-label">USC Charge:</span> <span class="card-row-value">€{{ number_format($payslip->usc, 2) }}</span></div>
                    <div class="card-row"><span class="card-row-label">PRSI Employee:</span> <span class="card-row-value">€{{ number_format($payslip->prsi, 2) }}</span></div>
                    <div class="card-row card-total"><span class="card-row-label">Net Pay:</span> <span class="card-row-value">€{{ number_format($payslip->net_pay, 2) }}</span></div>
                </div>
            </td>

            <!-- 3. Accumulated Section -->
            <td class="comparison-col">
                <div class="card">
                    <div class="card-title">3. YTD Accumulated</div>
                    <div class="card-row"><span class="card-row-label">YTD Gross:</span> <span class="card-row-value">€{{ number_format($accumulated['gross_pay'], 2) }}</span></div>
                    <div class="card-row"><span class="card-row-label">YTD PAYE:</span> <span class="card-row-value">€{{ number_format($accumulated['paye'], 2) }}</span></div>
                    <div class="card-row"><span class="card-row-label">YTD USC:</span> <span class="card-row-value">€{{ number_format($accumulated['usc'], 2) }}</span></div>
                    <div class="card-row"><span class="card-row-label">YTD PRSI:</span> <span class="card-row-value">€{{ number_format($accumulated['prsi'], 2) }}</span></div>
                    <div class="card-row card-total"><span class="card-row-label">YTD Net:</span> <span class="card-row-value">€{{ number_format($accumulated['net_pay'], 2) }}</span></div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Detailed Earnings & Deductions Tables -->
    <table class="details-table-wrapper" style="border-collapse: collapse; width: 100%;">
        <tr>
            <!-- Earnings -->
            <td class="details-col" style="border:none;">
                <h2>Earnings Breakdown</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Amount (€)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Basic Pay {{ $payslip->hours_worked > 0 ? '(' . $payslip->hours_worked . ' hrs)' : '' }}</td>
                            <td>{{ number_format($payslip->gross_pay - $payslip->bonus, 2) }}</td>
                        </tr>
                        @if($payslip->bonus > 0)
                        <tr>
                            <td>Bonus / Allowances</td>
                            <td>{{ number_format($payslip->bonus, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="total-row">
                            <td>Gross Pay</td>
                            <td>€{{ number_format($payslip->gross_pay, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <h2>Employer Contributions</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Amount (€)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Employer PRSI (Class A)</td>
                            <td>{{ number_format($payslip->employer_prsi, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>Total Cost to Employer</td>
                            <td>€{{ number_format($payslip->gross_pay + $payslip->employer_prsi, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- Deductions & Net Box -->
            <td class="details-col" style="border:none;">
                <h2>Deductions Breakdown</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tax / Charge</th>
                            <th>Amount (€)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>PAYE (Income Tax)</td>
                            <td>{{ number_format($payslip->paye, 2) }}</td>
                        </tr>
                        <tr>
                            <td>USC (Universal Social Charge)</td>
                            <td>{{ number_format($payslip->usc, 2) }}</td>
                        </tr>
                        <tr>
                            <td>PRSI (Employee — Class A)</td>
                            <td>{{ number_format($payslip->prsi, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>Total Deductions</td>
                            <td>€{{ number_format($payslip->paye + $payslip->usc + $payslip->prsi, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="net-pay-box">
                    <div class="lbl">Net Take-Home Pay</div>
                    <div class="amt">€{{ number_format($payslip->net_pay, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Confidential Document &mdash; Generated {{ now()->format('d M Y, H:i') }} &mdash; payroll system
    </div>
</body>
</html>
