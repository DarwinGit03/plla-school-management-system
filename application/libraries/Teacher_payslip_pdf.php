<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Small dependency-free PDF output for finalized payroll snapshots. */
class Teacher_payslip_pdf
{
    // US half-letter / half-bond paper: 5.5 x 8.5 inches at 72 points per inch.
    private $width = 396;
    private $height = 612;
    private $content = '';
    private $pages = [];

    public function generate($item, array $deductions, $taxes = null)
    {
        $this->content = '';
        $this->pages = [];
        $this->draw_header($item, false);

        $this->text(30, 65, 'PAY PERIOD', 7, true, 'left', [0.35, 0.35, 0.35]);
        $this->text(30, 77, $item->period_start . ' to ' . $item->period_end, 8, true);
        $this->text(242, 65, 'PAY DATE', 7, true, 'left', [0.35, 0.35, 0.35]);
        $this->text(242, 77, $item->pay_date, 8, true);

        $this->text(30, 97, 'EMPLOYEE', 7, true, 'left', [0.35, 0.35, 0.35]);
        $this->text(30, 109, $item->employee_name_snapshot, 9, true);
        $this->text(30, 127, 'EMPLOYEE NO. ' . $item->employee_no_snapshot, 7);
        if (!empty($item->position_snapshot)) $this->text(205, 127, 'POSITION: ' . $item->position_snapshot, 7);

        $tableTop = 145;
        $this->rect(24, $tableTop, 348, 19, [0.91, 0.94, 0.97]);
        $this->text(31, $tableTop + 5, 'PAY DETAILS', 7, true);
        $this->text(367, $tableTop + 5, 'AMOUNT (PHP)', 7, true, 'right');

        $rowTop = $tableTop + 19;
        $rateLabel = $item->salary_type_snapshot === 'daily' ? 'Daily salary rate' : 'Monthly salary rate';
        $rate = $item->salary_type_snapshot === 'daily' ? $item->daily_salary_snapshot : $item->monthly_salary_snapshot;
        $rowTop += $this->row($rowTop, $rateLabel, $this->money($rate));
        $rowTop += $this->row($rowTop, 'Days attended', number_format((float) $item->payable_days, 2, '.', ''));
        $rowTop += $this->row($rowTop, 'Paid leave days', number_format((float) $item->paid_leave_days, 2, '.', ''));
        $rowTop += $this->row($rowTop, 'Days absent', number_format((float) $item->absence_days, 2, '.', ''));
        $rowTop += $this->row($rowTop, 'Basic pay', $this->money($item->basic_pay), true);

        $this->text(31, $rowTop + 2, 'DEDUCTIONS', 7, true, 'left', [0.35, 0.35, 0.35]);
        $rowTop += 15;

        $deductionRows = [];
        if ((float) $item->absence_deduction > 0) {
            $deductionRows[] = ['Absence deduction', $this->money($item->absence_deduction)];
        }
        foreach ($deductions as $deduction) {
            $label = $deduction->description;
            if (!empty($deduction->notes)) $label .= ' - ' . $deduction->notes;
            $deductionRows[] = [$label, $this->money($deduction->amount)];
        }
        foreach ([
            'SSS employee share' => (float) ($taxes->sss_amount ?? 0),
            'PhilHealth employee share' => (float) ($taxes->philhealth_amount ?? 0),
            'Pag-IBIG employee share' => (float) ($taxes->pagibig_amount ?? 0),
            'Withholding tax' => (float) ($taxes->withholding_tax_amount ?? 0)
        ] as $label => $amount) {
            if ($amount > 0) $deductionRows[] = [$label, $this->money($amount)];
        }
        if (!$deductionRows) $deductionRows[] = ['No deductions', '0.00'];

        foreach ($deductionRows as $deductionRow) {
            $lineCount = count($this->wrap_label($deductionRow[0]));
            $estimatedHeight = max(15, $lineCount * 9 + 5);
            if ($rowTop + $estimatedHeight + 51 > $this->height - 20) {
                $this->start_continued_page($item);
                $rowTop = 86;
            }
            $rowTop += $this->row($rowTop, $deductionRow[0], $deductionRow[1]);
        }

        if ($rowTop + 51 > $this->height - 20) {
            $this->start_continued_page($item);
            $rowTop = 86;
        }
        $this->draw_totals($item, $rowTop);
        $this->text(198, 594, 'Generated from the school payroll system.', 6, false, 'center', [0.4, 0.4, 0.4]);
        $this->pages[] = $this->content;

        return $this->make_pdf();
    }

    private function draw_header($item, $continued)
    {
        $this->text(198, 19, 'PRECIOUS LITTLE LIGHTS ACADEMY', 10, true, 'center');
        $this->text(198, 36, $continued ? 'TEACHER PAYSLIP - CONTINUED' : 'TEACHER PAYSLIP', 8, true, 'center', [0.08, 0.35, 0.75]);
        $this->line(24, 51, 372, 51);
        if ($continued) {
            $this->text(30, 59, $item->employee_name_snapshot . '  |  ' . $item->period_start . ' to ' . $item->period_end, 7, true);
            $this->text(30, 75, 'DEDUCTIONS (CONTINUED)', 7, true, 'left', [0.35, 0.35, 0.35]);
        }
    }

    private function start_continued_page($item)
    {
        $this->text(198, 594, 'Continued on next page.', 6, false, 'center', [0.4, 0.4, 0.4]);
        $this->pages[] = $this->content;
        $this->content = '';
        $this->draw_header($item, true);
    }

    private function draw_totals($item, $top)
    {
        $this->line(24, $top + 2, 372, $top + 2);
        $this->text(31, $top + 8, 'TOTAL DEDUCTIONS', 8, true);
        $this->text(367, $top + 8, $this->money($item->total_deductions), 8, true, 'right');
        $this->rect(24, $top + 22, 348, 29, [0.91, 0.96, 0.92]);
        $this->text(31, $top + 31, 'NET PAY', 9, true);
        $this->text(367, $top + 31, 'PHP ' . $this->money($item->net_pay), 9, true, 'right');
    }

    private function row($top, $label, $amount, $bold = false)
    {
        $lines = $this->wrap_label($label);
        $size = 7;
        foreach ($lines as $index => $line) {
            $this->text(31, $top + 3 + ($index * 9), $line, $size, $bold);
        }
        $this->text(367, $top + 3, $amount, $size, $bold, 'right');
        $height = max(15, count($lines) * 9 + 5);
        $this->line(24, $top + $height, 372, $top + $height);
        return $height;
    }

    private function wrap_label($label)
    {
        return explode("\n", wordwrap((string) $label, 74, "\n", true));
    }

    private function money($amount)
    {
        return number_format((float) $amount, 2, '.', ',');
    }

    private function text($x, $top, $value, $size = 8, $bold = false, $align = 'left', $color = [0, 0, 0])
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
        $pageCount = count($this->pages);
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>'
        ];
        $pageReferences = [];
        for ($index = 0; $index < $pageCount; $index++) {
            $pageId = 3 + ($index * 2);
            $streamId = $pageId + 1;
            $pageReferences[] = $pageId . ' 0 R';
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $this->width . ' ' . $this->height . '] /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >> /F2 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >> >> >> /Contents ' . $streamId . ' 0 R >>';
            $objects[$streamId] = "<< /Length " . strlen($this->pages[$index]) . ">>\nstream\n" . $this->pages[$index] . "endstream";
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageReferences) . '] /Count ' . $pageCount . ' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $size = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 " . $size . "\n0000000000 65535 f \n";
        for ($id = 1; $id < $size; $id++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        return $pdf . "trailer\n<< /Size " . $size . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
    }
}
