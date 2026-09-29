<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Small dependency-free PDF writer for half-bond landscape payment statements. */
class Payment_statement_pdf
{
    private $width = 792;
    private $height = 396;
    private $content = '';

    public function generate($student, array $payments, $primary_guardian = null, array $signer = [])
    {
        $payments = array_values(array_filter($payments, function ($payment) {
            return isset($payment->status) && $payment->status === 'posted';
        }));

        $remaining_by_fee = [];
        foreach ($payments as $payment) {
            $fee_id = (int) $payment->enrollment_fee_id;
            if (!array_key_exists($fee_id, $remaining_by_fee)) {
                $remaining_by_fee[$fee_id] = (float) $payment->assessed_amount;
            }
            $remaining_by_fee[$fee_id] = max(
                0,
                $remaining_by_fee[$fee_id] - (float) $payment->amount_paid
            );
            $payment->balance_after_payment = $remaining_by_fee[$fee_id];
        }

        $pages = [];
        $chunks = array_chunk($payments, 8);
        if (!$chunks) {
            $chunks = [[]];
        }
        $all_total = array_sum(array_map(function ($payment) {
            return $payment->status === 'posted' ? (float) $payment->amount_paid : 0;
        }, $payments));
        $total_assessed = isset($student->total_assessed)
            ? (float) $student->total_assessed
            : array_sum(array_map(function ($payment) { return (float) $payment->assessed_amount; }, $payments));
        $total_paid = isset($student->total_paid) ? (float) $student->total_paid : $all_total;
        $total_balance = isset($student->balance)
            ? (float) $student->balance
            : max(0, $total_assessed - $total_paid);

        foreach ($chunks as $page_index => $chunk) {
            $this->content = '';
            $this->draw_statement_page(
                $student,
                $primary_guardian,
                $signer,
                $chunk,
                $page_index + 1,
                count($chunks),
                $total_assessed,
                $total_paid,
                $total_balance
            );
            $pages[] = $this->content;
        }

        return $this->make_pdf($pages);
    }

    private function draw_statement_page($student, $primary_guardian, array $signer, array $payments, $page_number, $page_count, $total_assessed, $total_paid, $total_balance)
    {
        $this->text(396, 12, 'PRECIOUS LITTLE LIGHTS ACADEMY', 11, true, 'center');
        $this->text(396, 26, 'RECORD OF PAYMENTS', 10, true, 'center', [0.55, 0.12, 0.12]);
        $years = array_values(array_unique(array_map(function ($payment) {
            return (string) $payment->academic_year;
        }, $payments)));
        $year_label = count($years) === 1 ? $years[0] : (count($years) ? 'Multiple school years' : 'No payment records');
        $this->text(396, 40, 'SCHOOL YEAR ' . $year_label, 9, true, 'center');

        $this->rect(34, 51, 724, 25);
        $this->line(276, 51, 276, 76);
        $this->line(518, 51, 518, 76);
        $this->text(42, 59, strtoupper($student->last_name ?? ''), 9, true);
        $this->text(284, 59, strtoupper($student->first_name ?? ''), 9, true);
        $middle_initial = !empty($student->middle_name)
            ? strtoupper(function_exists('mb_substr') ? mb_substr($student->middle_name, 0, 1) : substr($student->middle_name, 0, 1)) . '.'
            : '';
        $this->text(526, 59, $middle_initial, 9, true);
        $this->text(155, 79, 'SURNAME', 6, false, 'center');
        $this->text(396, 79, 'FIRST NAME', 6, false, 'center');
        $this->text(638, 79, 'MIDDLE INITIAL', 6, false, 'center');

        $modes = array_values(array_unique(array_map(function ($payment) {
            return ucwords(str_replace('_', ' ', (string) $payment->payment_mode));
        }, $payments)));
        $mode_label = count($modes) === 1 ? $modes[0] : (count($modes) ? 'Multiple' : 'Not recorded');
        $this->text(42, 94, 'STUDENT NO. ' . ($student->student_no ?? '') . '    ' . $this->fit_text($student->grade_section ?? 'Grade : Section not recorded', 90), 7);
        $this->text(42, 106, 'MODE OF PAYMENT: ' . $mode_label, 8, true);
        $table_x = 34;
        $table_y = 117;
        $column_widths = [55, 95, 120, 74, 85, 85, 85, 125];
        $this->draw_table_heading($table_x, $table_y, $column_widths);
        foreach ($payments as $row_index => $payment) {
            $row_y = $table_y + 17 + ($row_index * 14);
            $this->rect($table_x, $row_y, array_sum($column_widths), 14);
            $column_x = $table_x;
            foreach ($column_widths as $column_width) {
                $column_x += $column_width;
                if ($column_x < $table_x + array_sum($column_widths)) {
                    $this->line($column_x, $row_y, $column_x, $row_y + 14);
                }
            }

            $particular = $payment->payment_label ?: ucwords(str_replace('_', ' ', $payment->fee_category));
            if (count($years) > 1) {
                $particular = $payment->academic_year . ' ' . $particular;
            }
            if ($payment->status !== 'posted') {
                $particular .= ' (' . strtoupper($payment->status) . ')';
                if ($payment->status === 'voided' && !empty($payment->void_reason)) {
                    $particular .= ': ' . $payment->void_reason;
                }
            }

            $values = [
                $payment->paid_at ? date('m/d/Y', strtotime($payment->paid_at)) : '',
                $this->fit_text(ucwords(str_replace('_', ' ', (string) $payment->fee_category)), 17),
                $this->fit_text($particular, 24),
                $this->fit_text(ucwords(str_replace('_', ' ', (string) $payment->payment_method)), 13),
                $this->fit_text($payment->reference_number ?: '—', 17),
                number_format((float) $payment->amount_paid, 2),
                number_format((float) $payment->balance_after_payment, 2),
                $this->fit_text($payment->recorded_by_name ?? 'Unknown user', 21)
            ];
            $column_x = $table_x;
            foreach ($values as $column_index => $value) {
                $this->text($column_x + 3, $row_y + 4, $value, 6);
                $column_x += $column_widths[$column_index];
            }
        }

        $table_bottom = $table_y + 17 + (count($payments) * 14);
        $summary_top = $table_bottom + 6;
        $this->rect(34, $summary_top, 724, 39, [0.95, 0.96, 0.97]);
        $this->line(34, $summary_top + 13, 758, $summary_top + 13);
        $this->line(34, $summary_top + 26, 758, $summary_top + 26);
        $this->text(44, $summary_top + 4, 'Total posted payments', 7, true);
        $this->text(748, $summary_top + 4, 'PHP ' . number_format($total_paid, 2), 7, true, 'right');
        $this->text(44, $summary_top + 17, 'Total assessed fees', 7);
        $this->text(748, $summary_top + 17, 'PHP ' . number_format($total_assessed, 2), 7, false, 'right');
        $this->text(44, $summary_top + 30, 'Remaining balance', 7, true);
        $this->text(748, $summary_top + 30, 'PHP ' . number_format($total_balance, 2), 7, true, 'right');
        $this->text(748, $summary_top + 43, 'Page ' . $page_number . ' of ' . $page_count, 6, false, 'right');
        $this->text(42, $summary_top + 55, 'I certify that the information above is true and correct to the best of my knowledge. I authorize Precious Little Lights Academy to use my child\'s details for school records. This information will be treated as confidential in accordance with the Data Privacy Act of 2012.', 6);

        $guardian_name = trim(($primary_guardian->first_name ?? '') . ' ' . ($primary_guardian->last_name ?? ''));
        $signer_name = trim((string) ($signer['name'] ?? ''));
        $signer_role = trim((string) ($signer['role'] ?? 'AUTHORIZED SCHOOL REPRESENTATIVE'));
        if ($guardian_name !== '') {
            $this->text(173, 346, $this->fit_text($guardian_name, 38), 7, true, 'center');
        }
        if ($signer_name !== '') {
            $this->text(608, 346, $this->fit_text($signer_name, 38), 7, true, 'center');
        }
        $this->line(42, 361, 305, 361);
        $this->line(477, 361, 740, 361);
        $this->text(173, 367, 'PARENT / GUARDIAN - ' . ($guardian_name !== '' ? 'PRIMARY CONTACT' : 'SIGNATURE'), 6, false, 'center');
        $this->text(608, 367, $signer_role . ' SIGNATURE', 6, false, 'center');
    }

    private function draw_table_heading($x, $y, array $column_widths)
    {
        $width = array_sum($column_widths);
        $this->rect($x, $y, $width, 17, [0.90, 0.91, 0.92]);
        $labels = ['DATE', 'CATEGORY', 'PARTICULAR', 'PAYMENT MODE', 'REFERENCE #', 'AMOUNT PAID', 'BALANCE', 'RECORDED BY'];
        $column_x = $x;
        foreach ($column_widths as $index => $column_width) {
            if ($index > 0) {
                $this->line($column_x, $y, $column_x, $y + 17);
            }
            $this->text($column_x + 3, $y + 5, $labels[$index], 6, true);
            $column_x += $column_width;
        }
    }

    private function fit_text($value, $max_length)
    {
        $value = (string) $value;
        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
        if ($length <= $max_length) {
            return $value;
        }
        $short = function_exists('mb_substr')
            ? mb_substr($value, 0, $max_length - 3, 'UTF-8')
            : substr($value, 0, $max_length - 3);
        return $short . '...';
    }

    private function text($x, $top, $value, $size = 10, $bold = false, $align = 'left', $color = [0, 0, 0])
    {
        $value = $this->pdf_text((string) $value);
        $width = strlen($value) * $size * 0.48;
        if ($align === 'center') {
            $x -= $width / 2;
        } elseif ($align === 'right') {
            $x -= $width;
        }
        $font = $bold ? 'F2' : 'F1';
        $this->content .= sprintf("%.3f %.3f %.3f rg BT /%s %d Tf %.2f %.2f Td (%s) Tj ET\n", $color[0], $color[1], $color[2], $font, $size, $x, $this->height - $top - $size, $this->escape($value));
    }

    private function rect($x, $top, $width, $height, $fill = null)
    {
        $bottom = $this->height - $top - $height;
        if ($fill) {
            $this->content .= sprintf("%.3f %.3f %.3f rg %.2f %.2f %.2f %.2f re B\n", $fill[0], $fill[1], $fill[2], $x, $bottom, $width, $height);
        } else {
            $this->content .= sprintf("0 0 0 RG 0.7 w %.2f %.2f %.2f %.2f re S\n", $x, $bottom, $width, $height);
        }
    }

    private function line($x1, $top1, $x2, $top2)
    {
        $this->content .= sprintf("0 0 0 RG 0.7 w %.2f %.2f m %.2f %.2f l S\n", $x1, $this->height - $top1, $x2, $this->height - $top2);
    }

    private function pdf_text($value)
    {
        $value = str_replace(['₱', '—', '–', '·'], ['PHP ', '-', '-', '-'], $value);
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
            if ($converted !== false) {
                return $converted;
            }
        }
        return preg_replace('/[^\x20-\x7E]/', '?', $value);
    }

    private function escape($value)
    {
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], $value);
    }

    private function make_pdf(array $pages)
    {
        $objects = [1 => '<< /Type /Catalog /Pages 2 0 R >>'];
        $kids = [];
        $next_id = 3;
        foreach ($pages as $page_content) {
            $page_id = $next_id++;
            $stream_id = $next_id++;
            $kids[] = $page_id . ' 0 R';
            $objects[$page_id] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 792 396] /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >> /F2 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >> >> >> /Contents ' . $stream_id . ' 0 R >>';
            $objects[$stream_id] = "<< /Length " . strlen($page_content) . ">>\nstream\n" . $page_content . "endstream";
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';
        $info_id = $next_id;
        $objects[$info_id] = '<< /Title (Payment Record View) >>';
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R /ViewerPreferences << /DisplayDocTitle true >> >>';
        ksort($objects);
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
        }
        $xref_offset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R /Info " . $info_id . " 0 R >>\nstartxref\n" . $xref_offset . "\n%%EOF";
        return $pdf;
    }
}
