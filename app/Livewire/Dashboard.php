<?php

namespace App\Livewire;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\TaxLiability;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    // Selected payslip view state
    public $selectedEmployeeId = null;
    public $selectedPayslipId = null;

    // Listeners to refresh data when updates occur
    protected $listeners = [
        'employee-updated' => '$refresh',
        'payslip-created' => '$refresh',
    ];

    public function render()
    {
        // 1. Calculations for KPIs
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('active', true)->count();
        $totalGrossPaid = Payslip::sum('gross_pay');
        $totalNetPaid = Payslip::sum('net_pay');
        
        $totalPayeLiabilities = Payslip::sum('paye');
        $totalUscLiabilities = Payslip::sum('usc');
        $totalPrsiLiabilities = Payslip::sum('prsi') + Payslip::sum('employer_prsi');
        $totalLiability = $totalPayeLiabilities + $totalUscLiabilities + $totalPrsiLiabilities;

        // 2. Department distribution data
        $departmentData = Employee::select('department', DB::raw('count(*) as count'))
            ->groupBy('department')
            ->get();

        // 3. Wage distributions for charts
        $wageDist = Payslip::select('gross_pay')->get();

        // 4. Payslip Historical Auditing Segment (Previous, Current, Accumulated)
        $currentPayslip = null;
        $prevAccumulated = [
            'gross_pay' => 0.00,
            'paye' => 0.00,
            'usc' => 0.00,
            'prsi' => 0.00,
            'employer_prsi' => 0.00,
            'net_pay' => 0.00,
            'has_records' => false,
        ];
        $accumulated = [
            'gross_pay' => 0.00,
            'paye' => 0.00,
            'usc' => 0.00,
            'prsi' => 0.00,
            'employer_prsi' => 0.00,
            'net_pay' => 0.00,
        ];

        $employeePayslips = collect();

        if ($this->selectedEmployeeId) {
            $employeePayslips = Payslip::where('employee_id', $this->selectedEmployeeId)
                ->orderBy('period_end', 'desc')
                ->get();

            if ($this->selectedPayslipId) {
                $currentPayslip = Payslip::find($this->selectedPayslipId);
            } elseif ($employeePayslips->isNotEmpty()) {
                $currentPayslip = $employeePayslips->first();
                $this->selectedPayslipId = $currentPayslip->id;
            }

            if ($currentPayslip) {
                // Fetch Accumulated Previous Payslips YTD (excluding current)
                $year = date('Y', strtotime($currentPayslip->period_end));
                $prevAccumulatedQuery = Payslip::where('employee_id', $this->selectedEmployeeId)
                    ->whereYear('period_end', $year)
                    ->where('period_end', '<', $currentPayslip->period_start)
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
                $accumulatedQuery = Payslip::where('employee_id', $this->selectedEmployeeId)
                    ->whereYear('period_end', $year)
                    ->where('period_end', '<=', $currentPayslip->period_end)
                    ->get();

                $accumulated = [
                    'gross_pay' => $accumulatedQuery->sum('gross_pay'),
                    'paye' => $accumulatedQuery->sum('paye'),
                    'usc' => $accumulatedQuery->sum('usc'),
                    'prsi' => $accumulatedQuery->sum('prsi'),
                    'employer_prsi' => $accumulatedQuery->sum('employer_prsi'),
                    'net_pay' => $accumulatedQuery->sum('net_pay'),
                ];
            }
        }

        $this->dispatch('payroll-data-updated', [
            'departmentLabels' => $departmentData->pluck('department')->toArray(),
            'departmentCounts' => $departmentData->pluck('count')->toArray(),
            'paye' => $totalPayeLiabilities,
            'usc' => $totalUscLiabilities,
            'prsi' => $totalPrsiLiabilities,
        ]);

        return view('livewire.dashboard', [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'totalGrossPaid' => $totalGrossPaid,
            'totalNetPaid' => $totalNetPaid,
            'totalLiability' => $totalLiability,
            'totalPayeLiabilities' => $totalPayeLiabilities,
            'totalUscLiabilities' => $totalUscLiabilities,
            'totalPrsiLiabilities' => $totalPrsiLiabilities,
            'departmentData' => $departmentData,
            'employees' => Employee::all(),
            'employeePayslips' => $employeePayslips,
            'currentPayslip' => $currentPayslip,
            'prevAccumulated' => $prevAccumulated,
            'accumulated' => $accumulated,
        ]);
    }
}
