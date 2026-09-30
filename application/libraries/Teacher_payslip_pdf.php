<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Dependency-free single-page PDF output for finalized payroll snapshots. */
class Teacher_payslip_pdf
{
    private $width = 612;
    private $height = 792;
    private $content = '';

    public function generate($item, array $deductions, $taxes = null)
    {
        $this->content = '';
        $this->text(306, 42, 'PRECIOUS LITTLE LIGHTS ACADEMY', 16, true, 'center');
        $this->text(306, 68, 'TEACHER PAYSLIP', 12, true, 'center', [0.08, 0.35, 0.75]);
        $this->line(48, 91, 564, 91);

        $this->text(54, 111, 'PAY PERIOD', 8, true, 'left', [0.35, 0.35, 0.35]);
        $this->text(54, 126, $item->period_start . ' to ' . $item->period_end, 10, true);
        $this->text(360, 111, 'PAY DATE', 8, true, 'left', [0.35, 0.35, 0.35]);
        $this->text(360, 126, $item->pay_date, 10, true);

        $this->text(54, 159, 'EMPLOYEE', 8, true, 'left', [0.35, 0.35, 0.35]);
        $this->text(54, 174, $item->teacher_name_snapshot, 11, true);
        $this->text(360, 159, 'EMPLOYEE NO.', 8, true, 'left', [0.35, 0.35, 0.35]);
        $this->text(360, 174, $item->employee_no_snapshot, 10, true);
        if (!empty($item->position_snapshot)) {
            $this->text(54, 195, 'POSITION: ' . $item->position_snapshot, 9);
        }

        $top = 226;
        $this->rect(48, $top, 516, 25, [0.91, 0.94, 0.97]);
        $this->text(60, $top + 7, 'PAY DETAILS', 9, true);
        $this->text(550, $top + 7, 'AMOUNT (PHP)', 8, true, 'right');
        $rowTop = $top + 25;
        $rateLabel = $item->salary_type_snapshot === 'daily' ? 'Daily salary rate' : 'Monthly salary rate';
        $rate = $item->salary_type_snapshot === 'daily' ? $item->daily_salary_snapshot : $item->monthly_salary_snapshot;
        $this->row($rowTop, $rateLabel, $this->money($rate));
        $rowTop += 28;
        $this->row($rowTop, 'Days attended', number_format((float) $item->payable_days, 2, '.', ''));
        $rowTop += 28;
        $this->row($rowTop, 'Paid leave days', number_format((float) $item->paid_leave_days, 2, '.', ''));
        $rowTop += 28;
        $this->row($rowTop, 'Days absent', number_format((float) $item->absence_days, 2, '.', ''));
        $rowTop += 28;
        $this->row($rowTop, 'Basic pay', $this->money($item->basic_pay), true);
        $rowTop += 34;
        $this->text(60, $rowTop, 'DEDUCTIONS', 8, true, 'left', [0.35, 0.35, 0.35]);
        $rowTop += 18;

        if ((float) $item->absence_deduction > 0) {
            $this->row($rowTop, 'Absence deduction', $this->money($item->absence_deduction));
            $rowTop += 25;
        }
        foreach ($deductions as $deduction) {
            $label = $deduction->description;
            if (!empty($deduction->notes)) $label .= ' — ' . $deduction->notes;
            $this->row($rowTop, $label, $this->money($deduction->amount));
            $rowTop += 25;
        }
        foreach ([
            'SSS employee share' => (float) ($taxes->sss_amount ?? 0),
            'PhilHealth employee share' => (float) ($taxes->philhealth_amount ?? 0),
            'Pag-IBIG employee share' => (float) ($taxes->pagibig_amount ?? 0),
            'Withholding tax' => (float) ($taxes->withholding_tax_amount ?? 0)
        ] as $label => $amount) {
            if ($amount <= 0) continue;
            $this->row($rowTop, $label, $this->money($amount));
            $rowTop += 25;
        }
        if (!$deductions && (float) ($taxes->sss_amount ?? 0) <= 0 && (float) ($taxes->philhealth_amount ?? 0) <= 0 && (float) ($taxes->pagibig_amount ?? 0) <= 0 && (float) ($taxes->withholding_tax_amount ?? 0) <= 0 && (float) $item->absence_deduction <= 0) {
            $this->row($rowTop, 'No deductions', '0.00');
            $rowTop += 25;
        }

        $this->line(48, $rowTop + 5, 564, $rowTop + 5);
        $this->text(60, $rowTop + 19, 'TOTAL DEDUCTIONS', 9, true);
        $this->text(550, $rowTop + 19, $this->money($item->total_deductions), 9, true, 'right');
        $this->rect(48, $rowTop + 45, 516, 42, [0.91, 0.96, 0.92]);
        $this->text(60, $rowTop + 59, 'NET PAY', 11, true);
        $this->text(550, $rowTop + 59, 'PHP ' . $this->money($item->net_pay), 12, true, 'right');
        $this->text(306, 744, 'Generated from the school payroll system.', 8, false, 'center', [0.4, 0.4, 0.4]);

        return $this->make_pdf();
    }

    private function row($top, $label, $amount, $bold = false)
    {
        $this->text(60, $top + 7, $label, 9, $bold);
        $this->text(550, $top + 7, $amount, 9, $bold, 'right');
        $this->line(48, $top + 24, 564, $top + 24);
    }

    private function money($amount)
    {
        return number_format((float) $amount, 2, '.', ',');
    }

    private function text($x, $top, $value, $size = 10, $bold = false, $align = 'left', $color = [0, 0, 0])
    {
        $value = $this->pdf_text((string) $value);
        $width = strlen($value) * $size * 0.48;
        if ($align === 'center') $x -= $width / 2;
        if ($align === 'right') $x -= $width;
        $font = $bold ? 'F2' : 'F1';
        $this->content .= sprintf(
            "%.3f %.3f %.3f rg BT /%s %d Tf %.2f %.2f Td (%s) Tj ET\n",
            $color[0], $color[1], $color[2], $font, $size, $x,
            $this->height - $top - $size, $this->escape($value)
        );
    }

    private function rect($x, $top, $width, $height, array $fill)
    {
        $this->content .= sprintf(
            "%.3f %.3f %.3f rg %.2f %.2f %.2f %.2f re f\n",
            $fill[0], $fill[1], $fill[2], $x, $this->height - $top - $height, $width, $height
        );
    }

    private function line($x1, $top1, $x2, $top2)
    {
        $this->content .= sprintf(
            "0.72 0.75 0.79 RG 0.7 w %.2f %.2f m %.2f %.2f l S\n",
            $x1, $this->height - $top1, $x2, $this->height - $top2
        );
    }

    private function pdf_text($value)
    {
        $value = str_replace(['₱', '—', '–', '·'], ['PHP ', '-', '-', '-'], $value);
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
            if ($converted !== false) return $converted;
        }
        return preg_replace('/[^\x20-\x7E]/', '?', $value);
    }

    private function escape($value)
    {
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], $value);
    }

    private function make_pdf()
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >> /F2 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >> >> >> /Contents 4 0 R >>',
            4 => "<< /Length " . strlen($this->content) . ">>\nstream\n" . $this->content . "endstream",
            5 => '<< /Title (Teacher Payslip) >>'
        ];
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        for ($id = 1; $id <= 5; $id++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        return $pdf . "trailer\n<< /Size 6 /Root 1 0 R /Info 5 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
    }
}
