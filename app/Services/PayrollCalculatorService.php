<?php

namespace App\Services;

use App\Models\TaxSetting;

class PayrollCalculatorService
{
    /**
     * Calculate Irish Payroll taxes and net pay for a weekly period.
     * All thresholds are based on a weekly breakdown of standard Irish tax rules.
     *
     * @param float $grossPay Total gross pay for the week
     * @param float $annualTaxCredit Annual tax credit (default €3,750)
     * @param float $annualCutoffPoint Annual standard rate cutoff point (default €42,000)
     * @return array Containing break down of gross, PAYE, USC, PRSI (employee & employer), and net pay
     */
    public function calculateWeekly(float $grossPay, float $annualTaxCredit = null, float $annualCutoffPoint = null): array
    {
        // Use database values if not explicitly provided
        if (is_null($annualTaxCredit)) {
            $annualTaxCredit = TaxSetting::getValue('paye_annual_credit', 3750.00);
        }
        if (is_null($annualCutoffPoint)) {
            $annualCutoffPoint = TaxSetting::getValue('paye_annual_cutoff', 42000.00);
        }

        $payeStandardRate = TaxSetting::getValue('paye_standard_rate', 0.20);
        $payeHigherRate = TaxSetting::getValue('paye_higher_rate', 0.40);

        // 1. Calculate PAYE
        $weeklyCutoff = $annualCutoffPoint / 52.0;
        $weeklyTaxCredit = $annualTaxCredit / 52.0;

        $payeAt20 = min($grossPay, $weeklyCutoff) * $payeStandardRate;
        $payeAt40 = max(0.0, $grossPay - $weeklyCutoff) * $payeHigherRate;
        $grossPAYE = $payeAt20 + $payeAt40;
        $netPAYE = max(0.0, $grossPAYE - $weeklyTaxCredit);

        // 2. Calculate USC (Universal Social Charge) - Standard 2026 rates/thresholds
        $uscBand1Limit = TaxSetting::getValue('usc_band_1_limit', 12012.00) / 52.0;
        $uscBand1Rate = TaxSetting::getValue('usc_band_1_rate', 0.005);
        $uscBand2Limit = TaxSetting::getValue('usc_band_2_limit', 27382.00) / 52.0;
        $uscBand2Rate = TaxSetting::getValue('usc_band_2_rate', 0.02);
        $uscBand3Limit = TaxSetting::getValue('usc_band_3_limit', 70044.00) / 52.0;
        $uscBand3Rate = TaxSetting::getValue('usc_band_3_rate', 0.03);
        $uscBand4Rate = TaxSetting::getValue('usc_band_4_rate', 0.08);

        $usc = 0.0;

        // Band 1: Up to Band 1 Limit
        $taxableBand1 = min($grossPay, $uscBand1Limit);
        $usc += $taxableBand1 * $uscBand1Rate;

        // Band 2: From Band 1 Limit to Band 2 Limit
        $taxableBand2 = min(max(0.0, $grossPay - $uscBand1Limit), $uscBand2Limit - $uscBand1Limit);
        $usc += $taxableBand2 * $uscBand2Rate;

        // Band 3: From Band 2 Limit to Band 3 Limit
        $taxableBand3 = min(max(0.0, $grossPay - $uscBand2Limit), $uscBand3Limit - $uscBand2Limit);
        $usc += $taxableBand3 * $uscBand3Rate;

        // Band 4: Above Band 3 Limit
        $taxableBand4 = max(0.0, $grossPay - $uscBand3Limit);
        $usc += $taxableBand4 * $uscBand4Rate;

        // 3. Calculate PRSI (Pay Related Social Insurance - Class A)
        $prsiEmployeeRate = TaxSetting::getValue('prsi_employee_rate', 0.04);
        $prsiEmployeeThreshold = TaxSetting::getValue('prsi_employee_threshold', 352.00);
        $prsiEmployeeMaxCredit = TaxSetting::getValue('prsi_employee_max_credit', 12.00);
        $prsiEmployeeCreditTaperedThreshold = TaxSetting::getValue('prsi_employee_credit_tapered_threshold', 424.00);

        $prsiEmployerLowerRate = TaxSetting::getValue('prsi_employer_lower_rate', 0.088);
        $prsiEmployerLowerThreshold = TaxSetting::getValue('prsi_employer_lower_threshold', 441.00);
        $prsiEmployerHigherRate = TaxSetting::getValue('prsi_employer_higher_rate', 0.1105);

        $employeePRSI = 0.0;
        if ($grossPay > $prsiEmployeeThreshold) {
            $basePrsi = $grossPay * $prsiEmployeeRate;
            if ($grossPay <= $prsiEmployeeCreditTaperedThreshold) {
                $reduction = ($grossPay - $prsiEmployeeThreshold) / 6.0;
                $credit = max(0.0, $prsiEmployeeMaxCredit - $reduction);
                $employeePRSI = max(0.0, $basePrsi - $credit);
            } else {
                $employeePRSI = $basePrsi;
            }
        }

        // Employer PRSI
        $employerPRSI = 0.0;
        if ($grossPay > 0.0) {
            if ($grossPay <= $prsiEmployerLowerThreshold) {
                $employerPRSI = $grossPay * $prsiEmployerLowerRate;
            } else {
                $employerPRSI = $grossPay * $prsiEmployerHigherRate;
            }
        }

        // 4. Rounded Deductions & Net Wages
        $roundedGross = round($grossPay, 2);
        $roundedPaye = round($netPAYE, 2);
        $roundedUsc = round($usc, 2);
        $roundedPrsi = round($employeePRSI, 2);
        $roundedEmployerPrsi = round($employerPRSI, 2);

        $netWages = $roundedGross - $roundedPaye - $roundedUsc - $roundedPrsi;

        return [
            'gross_pay' => $roundedGross,
            'paye' => $roundedPaye,
            'usc' => $roundedUsc,
            'prsi' => $roundedPrsi,
            'employer_prsi' => $roundedEmployerPrsi,
            'net_pay' => round($netWages, 2),
            'meta' => [
                'paye_gross' => round($grossPAYE, 2),
                'paye_credit' => round($weeklyTaxCredit, 2),
                'paye_cutoff' => round($weeklyCutoff, 2),
            ]
        ];
    }
}

