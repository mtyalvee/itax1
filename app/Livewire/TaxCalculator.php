<?php

namespace App\Livewire;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\TaxLiability;
use App\Services\PayrollCalculatorService;
use Livewire\Component;

class TaxCalculator extends Component
{
    // Inputs
    public $selectedEmployeeId = null;
    public $hoursWorked = 37.5;
    public $overtimeHours = 0;
    public $bonus = 0.00;
    public $periodStart;
    public $periodEnd;

    // Calculation results
    public $calculation = null;
    public $lastSavedPayslipId = null;

    // Sorting & Filtering properties
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $searchName = '';
    public $searchDepartment = '';
    public $searchPpsn = '';
    public $searchPeriodFrom = '';
    public $searchPeriodTo = '';
    public $searchGross = '';
    public $searchPaye = '';
    public $searchUsc = '';
    public $searchEePrsi = '';
    public $searchErPrsi = '';
    public $searchDeductions = '';
    public $searchNet = '';

    // Edit mode property
    public $editingPayslipId = null;

    // Modal state for processing a new computation
    public $showProcessModal = false;

    protected $rules = [
        'selectedEmployeeId' => 'required|exists:employees,id',
        'hoursWorked' => 'required|numeric|min:0',
        'overtimeHours' => 'required|numeric|min:0',
        'bonus' => 'required|numeric|min:0',
        'periodStart' => 'required|date',
        'periodEnd' => 'required|date|after_or_equal:periodStart',
    ];

    public function mount()
    {
        $this->periodStart = now()->startOfWeek()->format('Y-m-d');
        $this->periodEnd = now()->endOfWeek()->format('Y-m-d');
    }

    public function updated($propertyName)
    {
        // Whenever inputs update, run the calculation if employee is selected
        if (in_array($propertyName, ['selectedEmployeeId', 'hoursWorked', 'overtimeHours', 'bonus'])) {
            $this->runCalculation();
        }
    }

    public function runCalculation()
    {
        if (!$this->selectedEmployeeId) {
            $this->calculation = null;
            return;
        }

        $employee = Employee::find($this->selectedEmployeeId);
        if (!$employee) {
            $this->calculation = null;
            return;
        }

        // Calculate Gross Pay:
        // Basic: if hourly_rate > 0, basic = hourly_rate * hoursWorked. Otherwise, weekly share of annual salary: salary / 52.
        $basicPay = 0.0;
        if (floatval($employee->hourly_rate) > 0) {
            $basicPay = floatval($employee->hourly_rate) * floatval($this->hoursWorked);
        } else {
            $basicPay = floatval($employee->salary) / 52.0;
        }

        // Overtime: 1.5x of hourly rate. If hourly rate is 0, we can calculate standard rate = salary / 52 / 37.5
        $hourlyRateForOvertime = floatval($employee->hourly_rate) > 0
            ? floatval($employee->hourly_rate)
            : (floatval($employee->salary) / 52.0 / 37.5);

        $overtimePay = floatval($this->overtimeHours) * ($hourlyRateForOvertime * 1.5);
        $bonusPay = floatval($this->bonus);

        $totalGross = $basicPay + $overtimePay + $bonusPay;

        // Run tax computation using the service
        $calculator = new PayrollCalculatorService();
        $this->calculation = $calculator->calculateWeekly(
            $totalGross,
            floatval($employee->tax_credit),
            floatval($employee->cutoff_point)
        );

        // Add additional detail metadata for display steps
        $this->calculation['basic_pay'] = round($basicPay, 2);
        $this->calculation['overtime_pay'] = round($overtimePay, 2);
        $this->calculation['bonus_pay'] = $bonusPay;
        $this->calculation['hourly_rate_overtime'] = round($hourlyRateForOvertime * 1.5, 2);
    }

    public function savePayslip()
    {
        $this->validate();
        
        $this->runCalculation();

        if (!$this->calculation) {
            return;
        }

        // Prevent duplicate payslips for the same period (excluding current record if editing)
        $existsQuery = Payslip::where('employee_id', $this->selectedEmployeeId)
            ->whereDate('period_start', $this->periodStart)
            ->whereDate('period_end', $this->periodEnd);

        if ($this->editingPayslipId) {
            $existsQuery->where('id', '!=', $this->editingPayslipId);
        }

        if ($existsQuery->exists()) {
            session()->flash('error', 'A payslip has already been processed for this employee for the selected period.');
            return;
        }

        if ($this->editingPayslipId) {
            // Update existing payslip
            $payslip = Payslip::find($this->editingPayslipId);
            $payslip->update(
                [
                    'employee_id' => $this->selectedEmployeeId,
                    'period_start' => $this->periodStart,
                    'period_end' => $this->periodEnd,
                    'gross_pay' => $this->calculation['gross_pay'],
                    'paye' => $this->calculation['paye'],
                    'usc' => $this->calculation['usc'],
                    'prsi' => $this->calculation['prsi'],
                    'employer_prsi' => $this->calculation['employer_prsi'],
                    'net_pay' => $this->calculation['net_pay'],
                    'hours_worked' => $this->hoursWorked,
                    'overtime_hours' => $this->overtimeHours,
                    'bonus' => $this->bonus,
                    'status' => 'processed',
                ]
            );
            session()->flash('message', 'Payslip updated successfully.');
        } else {
            // Create new payslip
            $payslip = Payslip::create(
                [
                    'employee_id' => $this->selectedEmployeeId,
                    'period_start' => $this->periodStart,
                    'period_end' => $this->periodEnd,
                    'gross_pay' => $this->calculation['gross_pay'],
                    'paye' => $this->calculation['paye'],
                    'usc' => $this->calculation['usc'],
                    'prsi' => $this->calculation['prsi'],
                    'employer_prsi' => $this->calculation['employer_prsi'],
                    'net_pay' => $this->calculation['net_pay'],
                    'hours_worked' => $this->hoursWorked,
                    'overtime_hours' => $this->overtimeHours,
                    'bonus' => $this->bonus,
                    'status' => 'processed',
                ]
            );
            session()->flash('message', 'Payslip processed successfully.');
        }

        // Record or update Monthly Tax Liability
        $taxPeriod = date('Y-m', strtotime($this->periodStart));
        
        // Recalculate total liability for this tax period from all processed payslips
        $allPayslipsInPeriod = Payslip::where('period_start', '>=', date('Y-m-01', strtotime($this->periodStart)))
            ->where('period_end', '<=', date('Y-m-t', strtotime($this->periodStart)))
            ->get();

        $totalPaye = $allPayslipsInPeriod->sum('paye');
        $totalUsc = $allPayslipsInPeriod->sum('usc');
        $totalPrsiEmployee = $allPayslipsInPeriod->sum('prsi');
        $totalPrsiEmployer = $allPayslipsInPeriod->sum('employer_prsi');
        $totalLiability = $totalPaye + $totalUsc + $totalPrsiEmployee + $totalPrsiEmployer;

        TaxLiability::updateOrCreate(
            ['tax_period' => $taxPeriod],
            [
                'paye' => $totalPaye,
                'usc' => $totalUsc,
                'prsi_employee' => $totalPrsiEmployee,
                'prsi_employer' => $totalPrsiEmployer,
                'total_liability' => $totalLiability,
            ]
        );

        $this->lastSavedPayslipId = $payslip->id;
        session()->flash('message', 'Payslip processed successfully and liability ledger updated.');
        $this->dispatch('payslip-created');
    }

    public function addOrUpdatePayslip()
    {
        $this->validate();
        
        $this->runCalculation();

        if (!$this->calculation) {
            return;
        }

        if (!$this->editingPayslipId) {
            $this->savePayslip();
            return;
        }

        $originalPayslip = Payslip::find($this->editingPayslipId);
        if (!$originalPayslip) {
            session()->flash('error', 'Original payslip record not found.');
            return;
        }

        $originalStart = $originalPayslip->period_start->format('Y-m-d');
        $originalEnd = $originalPayslip->period_end->format('Y-m-d');

        if ($this->periodStart === $originalStart && $this->periodEnd === $originalEnd) {
            // Same wage period -> save with new values if any
            $originalPayslip->update(
                [
                    'employee_id' => $this->selectedEmployeeId,
                    'gross_pay' => $this->calculation['gross_pay'],
                    'paye' => $this->calculation['paye'],
                    'usc' => $this->calculation['usc'],
                    'prsi' => $this->calculation['prsi'],
                    'employer_prsi' => $this->calculation['employer_prsi'],
                    'net_pay' => $this->calculation['net_pay'],
                    'hours_worked' => $this->hoursWorked,
                    'overtime_hours' => $this->overtimeHours,
                    'bonus' => $this->bonus,
                    'status' => 'processed',
                ]
            );
            $msg = 'existing record updated';
            $payslip = $originalPayslip;
        } else {
            // Different wage period -> add new record
            // Check for duplicate for this new period
            $existsQuery = Payslip::where('employee_id', $this->selectedEmployeeId)
                ->whereDate('period_start', $this->periodStart)
                ->whereDate('period_end', $this->periodEnd);

            if ($existsQuery->exists()) {
                session()->flash('error', 'A payslip has already been processed for this employee for the selected period.');
                return;
            }

            $payslip = Payslip::create(
                [
                    'employee_id' => $this->selectedEmployeeId,
                    'period_start' => $this->periodStart,
                    'period_end' => $this->periodEnd,
                    'gross_pay' => $this->calculation['gross_pay'],
                    'paye' => $this->calculation['paye'],
                    'usc' => $this->calculation['usc'],
                    'prsi' => $this->calculation['prsi'],
                    'employer_prsi' => $this->calculation['employer_prsi'],
                    'net_pay' => $this->calculation['net_pay'],
                    'hours_worked' => $this->hoursWorked,
                    'overtime_hours' => $this->overtimeHours,
                    'bonus' => $this->bonus,
                    'status' => 'processed',
                ]
            );
            $msg = 'new record added';
        }

        // Record or update Monthly Tax Liability
        $taxPeriod = date('Y-m', strtotime($this->periodStart));
        
        $allPayslipsInPeriod = Payslip::where('period_start', '>=', date('Y-m-01', strtotime($this->periodStart)))
            ->where('period_end', '<=', date('Y-m-t', strtotime($this->periodStart)))
            ->get();

        $totalPaye = $allPayslipsInPeriod->sum('paye');
        $totalUsc = $allPayslipsInPeriod->sum('usc');
        $totalPrsiEmployee = $allPayslipsInPeriod->sum('prsi');
        $totalPrsiEmployer = $allPayslipsInPeriod->sum('employer_prsi');
        $totalLiability = $totalPaye + $totalUsc + $totalPrsiEmployee + $totalPrsiEmployer;

        TaxLiability::updateOrCreate(
            ['tax_period' => $taxPeriod],
            [
                'paye' => $totalPaye,
                'usc' => $totalUsc,
                'prsi_employee' => $totalPrsiEmployee,
                'prsi_employer' => $totalPrsiEmployer,
                'total_liability' => $totalLiability,
            ]
        );

        $this->lastSavedPayslipId = $payslip->id;
        session()->flash('message', $msg);
        $this->js("alert('$msg');");
        $this->dispatch('payslip-created');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function startNewComputation()
    {
        $this->editingPayslipId = null;
        $this->selectedEmployeeId = null;
        $this->hoursWorked = 37.5;
        $this->overtimeHours = 0;
        $this->bonus = 0.00;
        $this->calculation = null;
        $this->showProcessModal = true;
    }

    public function editPayslip($id)
    {
        $payslip = Payslip::find($id);
        if ($payslip) {
            $this->editingPayslipId = $payslip->id;
            $this->selectedEmployeeId = $payslip->employee_id;
            $this->hoursWorked = floatval($payslip->hours_worked);
            $this->overtimeHours = floatval($payslip->overtime_hours);
            $this->bonus = floatval($payslip->bonus);
            $this->periodStart = $payslip->period_start->format('Y-m-d');
            $this->periodEnd = $payslip->period_end->format('Y-m-d');
            
            $this->runCalculation();
            $this->showProcessModal = true;
        }
    }

    public function deletePayslip($id)
    {
        $payslip = Payslip::find($id);
        if ($payslip) {
            $periodStart = $payslip->period_start;
            $payslip->delete();

            // Recalculate liability ledger for that period
            $taxPeriod = date('Y-m', strtotime($periodStart));
            $allPayslipsInPeriod = Payslip::where('period_start', '>=', date('Y-m-01', strtotime($periodStart)))
                ->where('period_end', '<=', date('Y-m-t', strtotime($periodStart)))
                ->get();

            $totalPaye = $allPayslipsInPeriod->sum('paye');
            $totalUsc = $allPayslipsInPeriod->sum('usc');
            $totalPrsiEmployee = $allPayslipsInPeriod->sum('prsi');
            $totalPrsiEmployer = $allPayslipsInPeriod->sum('employer_prsi');
            $totalLiability = $totalPaye + $totalUsc + $totalPrsiEmployee + $totalPrsiEmployer;

            TaxLiability::updateOrCreate(
                ['tax_period' => $taxPeriod],
                [
                    'paye' => $totalPaye,
                    'usc' => $totalUsc,
                    'prsi_employee' => $totalPrsiEmployee,
                    'prsi_employer' => $totalPrsiEmployer,
                    'total_liability' => $totalLiability,
                ]
            );

            session()->flash('message', 'Payslip deleted successfully and liability ledger updated.');
            $this->dispatch('payslip-created');
        }
    }

    public function render()
    {
        $query = Payslip::select('payslips.*')
            ->join('employees', 'payslips.employee_id', '=', 'employees.id');

        // Apply filters
        if (!empty($this->searchName)) {
            $query->where('employees.name', 'like', '%' . $this->searchName . '%');
        }
        if (!empty($this->searchDepartment)) {
            $query->where('employees.department', 'like', '%' . $this->searchDepartment . '%');
        }
        if (!empty($this->searchPpsn)) {
            $query->where('employees.pps_number', 'like', '%' . $this->searchPpsn . '%');
        }
        if (!empty($this->searchPeriodFrom)) {
            $query->where('payslips.period_start', 'like', '%' . $this->searchPeriodFrom . '%');
        }
        if (!empty($this->searchPeriodTo)) {
            $query->where('payslips.period_end', 'like', '%' . $this->searchPeriodTo . '%');
        }
        if (!empty($this->searchGross)) {
            $query->where('payslips.gross_pay', 'like', '%' . $this->searchGross . '%');
        }
        if (!empty($this->searchPaye)) {
            $query->where('payslips.paye', 'like', '%' . $this->searchPaye . '%');
        }
        if (!empty($this->searchUsc)) {
            $query->where('payslips.usc', 'like', '%' . $this->searchUsc . '%');
        }
        if (!empty($this->searchEePrsi)) {
            $query->where('payslips.prsi', 'like', '%' . $this->searchEePrsi . '%');
        }
        if (!empty($this->searchErPrsi)) {
            $query->where('payslips.employer_prsi', 'like', '%' . $this->searchErPrsi . '%');
        }
        if (!empty($this->searchDeductions)) {
            $query->whereRaw('(payslips.paye + payslips.usc + payslips.prsi) like ?', ['%' . $this->searchDeductions . '%']);
        }
        if (!empty($this->searchNet)) {
            $query->where('payslips.net_pay', 'like', '%' . $this->searchNet . '%');
        }

        // Determine sorting column
        $sortColumn = $this->sortField;
        if (in_array($sortColumn, ['name', 'department', 'pps_number'])) {
            $sortColumn = 'employees.' . $sortColumn;
        } else {
            $sortColumn = 'payslips.' . $sortColumn;
        }

        $payslips = $query->orderBy($sortColumn, $this->sortDirection)->get();

        return view('livewire.tax-calculator', [
            'payslips' => $payslips,
            'employees' => Employee::where('active', true)->get()
        ]);
    }
}
