<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\TaxLiability;
use App\Services\PayrollCalculatorService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Seed default tax settings
        $settings = [
            // PAYE
            [
                'key' => 'paye_standard_rate',
                'value' => '0.20',
                'display_name' => 'PAYE Standard Rate',
                'category' => 'paye',
                'type' => 'percentage',
            ],
            [
                'key' => 'paye_higher_rate',
                'value' => '0.40',
                'display_name' => 'PAYE Higher Rate',
                'category' => 'paye',
                'type' => 'percentage',
            ],
            [
                'key' => 'paye_annual_cutoff',
                'value' => '42000.00',
                'display_name' => 'PAYE Standard Annual Cutoff Point',
                'category' => 'paye',
                'type' => 'amount',
            ],
            [
                'key' => 'paye_annual_credit',
                'value' => '3750.00',
                'display_name' => 'PAYE Annual Tax Credit',
                'category' => 'paye',
                'type' => 'amount',
            ],

            // USC
            [
                'key' => 'usc_band_1_limit',
                'value' => '12012.00',
                'display_name' => 'USC Band 1 Limit (Annual)',
                'category' => 'usc',
                'type' => 'amount',
            ],
            [
                'key' => 'usc_band_1_rate',
                'value' => '0.005',
                'display_name' => 'USC Band 1 Rate',
                'category' => 'usc',
                'type' => 'percentage',
            ],
            [
                'key' => 'usc_band_2_limit',
                'value' => '27382.00',
                'display_name' => 'USC Band 2 Limit (Annual)',
                'category' => 'usc',
                'type' => 'amount',
            ],
            [
                'key' => 'usc_band_2_rate',
                'value' => '0.02',
                'display_name' => 'USC Band 2 Rate',
                'category' => 'usc',
                'type' => 'percentage',
            ],
            [
                'key' => 'usc_band_3_limit',
                'value' => '70044.00',
                'display_name' => 'USC Band 3 Limit (Annual)',
                'category' => 'usc',
                'type' => 'amount',
            ],
            [
                'key' => 'usc_band_3_rate',
                'value' => '0.03',
                'display_name' => 'USC Band 3 Rate',
                'category' => 'usc',
                'type' => 'percentage',
            ],
            [
                'key' => 'usc_band_4_rate',
                'value' => '0.08',
                'display_name' => 'USC Band 4 Rate (Balance)',
                'category' => 'usc',
                'type' => 'percentage',
            ],

            // PRSI
            [
                'key' => 'prsi_employee_rate',
                'value' => '0.04',
                'display_name' => 'PRSI Employee Rate (Class A)',
                'category' => 'prsi',
                'type' => 'percentage',
            ],
            [
                'key' => 'prsi_employee_threshold',
                'value' => '352.00',
                'display_name' => 'PRSI Employee Weekly Threshold',
                'category' => 'prsi',
                'type' => 'amount',
            ],
            [
                'key' => 'prsi_employee_max_credit',
                'value' => '12.00',
                'display_name' => 'PRSI Employee Max Weekly Credit',
                'category' => 'prsi',
                'type' => 'amount',
            ],
            [
                'key' => 'prsi_employee_credit_tapered_threshold',
                'value' => '424.00',
                'display_name' => 'PRSI Employee Credit Tapered Weekly Limit',
                'category' => 'prsi',
                'type' => 'amount',
            ],
            [
                'key' => 'prsi_employer_lower_rate',
                'value' => '0.088',
                'display_name' => 'PRSI Employer Lower Rate',
                'category' => 'prsi',
                'type' => 'percentage',
            ],
            [
                'key' => 'prsi_employer_lower_threshold',
                'value' => '441.00',
                'display_name' => 'PRSI Employer Lower Weekly Threshold',
                'category' => 'prsi',
                'type' => 'amount',
            ],
            [
                'key' => 'prsi_employer_higher_rate',
                'value' => '0.1105',
                'display_name' => 'PRSI Employer Higher Rate',
                'category' => 'prsi',
                'type' => 'percentage',
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\TaxSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'display_name' => $setting['display_name'],
                    'category' => $setting['category'],
                    'type' => $setting['type'],
                ]
            );
        }

        // 1. Seed Employees
        $employees = [
            [
                'name' => 'Sean Higgins',
                'email' => 'sean.higgins@enterprise.ie',
                'pps_number' => '1234567HA',
                'department' => 'Software Development',
                'job_title' => 'Senior Architect',
                'hourly_rate' => 0.00,
                'salary' => 85000.00,
                'active' => true,
                'tax_credit' => 3750.00,
                'cutoff_point' => 42000.00,
            ],
            [
                'name' => 'Niamh Kelly',
                'email' => 'niamh.kelly@enterprise.ie',
                'pps_number' => '9876543KB',
                'department' => 'HR & Operations',
                'job_title' => 'HR Director',
                'hourly_rate' => 0.00,
                'salary' => 52000.00,
                'active' => true,
                'tax_credit' => 3750.00,
                'cutoff_point' => 42000.00,
            ],
            [
                'name' => 'Liam Murphy',
                'email' => 'liam.murphy@enterprise.ie',
                'pps_number' => '7654321MA',
                'department' => 'Customer Success',
                'job_title' => 'Support Engineer',
                'hourly_rate' => 24.50,
                'salary' => 0.00,
                'active' => true,
                'tax_credit' => 3750.00,
                'cutoff_point' => 42000.00,
            ],
            [
                'name' => 'Aoife Boyle',
                'email' => 'aoife.boyle@enterprise.ie',
                'pps_number' => '4567890BA',
                'department' => 'Marketing',
                'job_title' => 'Growth Manager',
                'hourly_rate' => 0.00,
                'salary' => 42000.00,
                'active' => true,
                'tax_credit' => 3750.00,
                'cutoff_point' => 42000.00,
            ],
        ];

        $calculator = new PayrollCalculatorService();
        $seededEmployees = [];

        foreach ($employees as $empData) {
            $seededEmployees[] = Employee::create($empData);
        }

        // 2. Seed Payslips for the last 3 weeks to show timeline and charts
        $weeks = [
            [
                'start' => date('Y-m-d', strtotime('monday last week - 2 weeks')),
                'end' => date('Y-m-d', strtotime('sunday last week - 2 weeks')),
            ],
            [
                'start' => date('Y-m-d', strtotime('monday last week - 1 week')),
                'end' => date('Y-m-d', strtotime('sunday last week - 1 week')),
            ],
            [
                'start' => date('Y-m-d', strtotime('monday last week')),
                'end' => date('Y-m-d', strtotime('sunday last week')),
            ],
        ];

        foreach ($seededEmployees as $emp) {
            foreach ($weeks as $week) {
                // Calculate gross
                if ($emp->hourly_rate > 0) {
                    $hours = 37.5;
                    $gross = $emp->hourly_rate * $hours;
                } else {
                    $hours = 0.0;
                    $gross = $emp->salary / 52.0;
                }

                // Add some overtime and bonus variation
                $overtime = 0.0;
                $bonus = 0.0;
                if ($emp->name === 'Sean Higgins' && $week['start'] === $weeks[2]['start']) {
                    $bonus = 250.00;
                }
                if ($emp->name === 'Liam Murphy') {
                    $overtime = 5.0; // 5 hours overtime
                    $hourlyRateForOvertime = $emp->hourly_rate;
                    $gross += $overtime * ($hourlyRateForOvertime * 1.5);
                }

                $gross += $bonus;

                $calc = $calculator->calculateWeekly($gross, $emp->tax_credit, $emp->cutoff_point);

                Payslip::create([
                    'employee_id' => $emp->id,
                    'gross_pay' => $calc['gross_pay'],
                    'paye' => $calc['paye'],
                    'usc' => $calc['usc'],
                    'prsi' => $calc['prsi'],
                    'employer_prsi' => $calc['employer_prsi'],
                    'net_pay' => $calc['net_pay'],
                    'hours_worked' => $hours,
                    'overtime_hours' => $overtime,
                    'bonus' => $bonus,
                    'period_start' => $week['start'],
                    'period_end' => $week['end'],
                    'status' => 'processed',
                ]);
            }
        }

        // 3. Compile Monthly Tax Liabilities for July 2026
        $allPayslips = Payslip::all();
        $taxPeriod = date('Y-m', strtotime('monday last week'));
        
        $totalPaye = $allPayslips->sum('paye');
        $totalUsc = $allPayslips->sum('usc');
        $totalPrsiEmployee = $allPayslips->sum('prsi');
        $totalPrsiEmployer = $allPayslips->sum('employer_prsi');
        $totalLiability = $totalPaye + $totalUsc + $totalPrsiEmployee + $totalPrsiEmployer;

        TaxLiability::create([
            'tax_period' => $taxPeriod,
            'paye' => $totalPaye,
            'usc' => $totalUsc,
            'prsi_employee' => $totalPrsiEmployee,
            'prsi_employer' => $totalPrsiEmployer,
            'total_liability' => $totalLiability,
        ]);
    }
}
