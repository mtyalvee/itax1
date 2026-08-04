<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Payslip;
use App\Livewire\TaxCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaxCalculatorAddOrUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_or_update_with_same_wage_period_updates_existing_record()
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'pps_number' => '1234567A',
            'salary' => 52000,
            'tax_credit' => 60,
            'cutoff_point' => 700,
            'department' => 'IT',
            'job_title' => 'Developer'
        ]);

        $payslip = Payslip::create([
            'employee_id' => $employee->id,
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-07',
            'gross_pay' => 1000,
            'paye' => 140,
            'usc' => 40,
            'prsi' => 40,
            'employer_prsi' => 110,
            'net_pay' => 780,
            'hours_worked' => 37.5,
            'overtime_hours' => 0,
            'bonus' => 0,
            'status' => 'processed'
        ]);

        Livewire::test(TaxCalculator::class)
            ->call('editPayslip', $payslip->id)
            ->set('hoursWorked', 40) // update some value
            ->call('addOrUpdatePayslip')
            ->assertHasNoErrors();

        $this->assertEquals(1, Payslip::count());
        $updatedPayslip = Payslip::first();
        $this->assertEquals(40, $updatedPayslip->hours_worked);
    }

    public function test_add_or_update_with_different_wage_period_adds_new_record()
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'pps_number' => '1234567A',
            'salary' => 52000,
            'tax_credit' => 60,
            'cutoff_point' => 700,
            'department' => 'IT',
            'job_title' => 'Developer'
        ]);

        $payslip = Payslip::create([
            'employee_id' => $employee->id,
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-07',
            'gross_pay' => 1000,
            'paye' => 140,
            'usc' => 40,
            'prsi' => 40,
            'employer_prsi' => 110,
            'net_pay' => 780,
            'hours_worked' => 37.5,
            'overtime_hours' => 0,
            'bonus' => 0,
            'status' => 'processed'
        ]);

        Livewire::test(TaxCalculator::class)
            ->call('editPayslip', $payslip->id)
            ->set('periodStart', '2026-07-08')
            ->set('periodEnd', '2026-07-14')
            ->call('addOrUpdatePayslip')
            ->assertHasNoErrors();

        $this->assertEquals(2, Payslip::count());
    }
}
