<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Small dependency-free PDF writer for payment statements (US Letter, landscape). */
class Payment_statement_pdf
{
    private $width = 792;
    private $height = 612;
    private $content = '';

    public function generate($student, array $payments)
    {
        $remaining_by_fee = [];
        foreach ($payments as $payment) {
            $fee_id = (int) $payment->enrollment_fee_id;
            if (!array_key_exists($fee_id, $remaining_by_fee)) {
                $remaining_by_fee[$fee_id] = (float) $payment->assessed_amount;
            }
            if ($payment->status === 'posted') {
                $remaining_by_fee[$fee_id] = max(
                    0,
                    $remaining_by_fee[$fee_id] - (float) $payment->amount_paid
                );
            }
            $payment->balance_after_payment = $remaining_by_fee[$fee_id];
        }

        $pages = [];
        $chunks = array_chunk($payments, 15);
        if (!$chunks) {
            $chunks = [[]];
        }
        $all_total = array_sum(array_map(function ($payment) {
            return $payment->status === 'posted' ? (float) $payment->amount_paid : 0;
        }, $payments));

        foreach ($chunks as $page_index => $chunk) {
            $this->content = '';
            $this->draw_statement_page($student, $guardian, $chunk, $page_index + 1, count($chunks), $all_total);
            $pages[] = $this->content;
        }

        return $this->make_pdf($pages);
    }

    private function draw_statement_page($student, $guardian, array $payments, $page_number, $page_count, $all_total)
    {
        $this->text(396, 30, 'PRECIOUS LITTLE LIGHTS ACADEMY', 16, true, 'center');
        $this->text(396, 51, 'RECORD OF PAYMENTS', 14, true, 'center', [0.55, 0.12, 0.12]);
        $years = array_values(array_unique(array_map(function ($payment) {
            return (string) $payment->academic_year;
        }, $payments)));
        $year_label = count($years) === 1 ? $years[0] : (count($years) ? 'Multiple school years' : 'No payment records');
        $this->text(396, 70, 'SCHOOL YEAR ' . $year_label, 12, true, 'center');

        $this->rect(34, 88, 724, 35);
        $this->line(276, 88, 276, 123);
        $this->line(518, 88, 518, 123);
        $this->text(42, 102, strtoupper($student->last_name ?? ''), 11, true);
        $this->text(284, 102, strtoupper($student->first_name ?? ''), 11, true);
        $middle_initial = !empty($student->middle_name)
            ? strtoupper(function_exists('mb_substr') ? mb_substr($student->middle_name, 0, 1) : substr($student->middle_name, 0, 1)) . '.'
            : '';
        $this->text(526, 102, $middle_initial, 11, true);
        $this->text(155, 137, 'SURNAME', 8, false, 'center');
        $this->text(396, 137, 'FIRST NAME', 8, false, 'center');
        $this->text(638, 137, 'MIDDLE INITIAL', 8, false, 'center');

        $modes = array_values(array_unique(array_map(function ($payment) {
            return ucwords(str_replace('_', ' ', (string) $payment->payment_mode));
        }, $payments)));
        $mode_label = count($modes) === 1 ? $modes[0] : (count($modes) ? 'Multiple' : 'Not recorded');
        $this->text(42, 157, 'STUDENT NO. ' . ($student->student_no ?? '') . '    LRN ' . ($student->lrn ?? '—'), 9);
        $this->text(42, 173, 'MODE OF PAYMENT: ' . $mode_label, 10, true);

        $table_x = 34;
        $table_y = 187;
        $column_widths = [62, 142, 100, 82, 100, 100, 138];
        $this->draw_table_heading($table_x, $table_y, $column_widths);
        foreach ($payments as $row_index => $payment) {
            $row_y = $table_y + 22 + ($row_index * 19);
            $this->rect($table_x, $row_y, array_sum($column_widths), 19);
            $column_x = $table_x;
            foreach ($column_widths as $column_width) {
                $column_x += $column_width;
                if ($column_x < $table_x + array_sum($column_widths)) {
                    $this->line($column_x, $row_y, $column_x, $row_y + 19);
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
                $this->fit_text($particular, 25),
                $this->fit_text($payment->receipt_number ?: '', 25),
                $this->fit_text(ucwords(str_replace('_', ' ', (string) $payment->payment_method)), 14),
                $this->fit_text($payment->reference_number ?: '—', 17),
                number_format((float) $payment->amount_paid, 2),
                number_format((float) $payment->balance_after_payment, 2)
            ];
            $column_x = $table_x;
            foreach ($values as $column_index => $value) {
                $this->text($column_x + 4, $row_y + 5, $value, 7);
                $column_x += $column_widths[$column_index];
            }
        }

        $total = array_sum(array_map(function ($payment) {
            return $payment->status === 'posted' ? (float) $payment->amount_paid : 0;
        }, $payments));
        $table_bottom = $table_y + 22 + (count($payments) * 19);
        $this->text(42, $table_bottom + 9, 'TOTAL POSTED PAYMENTS ON THIS PAGE: PHP ' . number_format($total, 2), 9, true);
        $this->text(750, $table_bottom + 9, 'Page ' . $page_number . ' of ' . $page_count, 8, false, 'right');
        $this->text(42, $table_bottom + 24, 'TOTAL POSTED PAYMENTS (ALL PAGES): PHP ' . number_format($all_total, 2), 9, true);
        $this->text(42, $table_bottom + 39, 'Balance is the remaining amount on each fee item after the listed payment.', 8);
        $this->line(42, 578, 305, 578);
        $this->line(477, 578, 740, 578);
        $this->text(173, 585, 'PARENT / GUARDIAN SIGNATURE', 8, false, 'center');
        $this->text(608, 585, 'PRINCIPAL / ADMIN SIGNATURE', 8, false, 'center');
    }

    private function draw_table_heading($x, $y, array $column_widths)
    {
        $width = array_sum($column_widths);
        $this->rect($x, $y, $width, 22, [0.90, 0.91, 0.92]);
        $labels = ['DATE', 'PARTICULAR', 'RECEIPT #', 'PAYMENT MODE', 'REFERENCE #', 'AMOUNT PAID', 'BALANCE'];
        $column_x = $x;
        foreach ($column_widths as $index => $column_width) {
            if ($index > 0) {
                $this->line($column_x, $y, $column_x, $y + 22);
            }
            $this->text($column_x + 4, $y + 7, $labels[$index], 7, true);
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
            $objects[$page_id] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 792 612] /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >> /F2 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >> >> >> /Contents ' . $stream_id . ' 0 R >>';
            $objects[$stream_id] = "<< /Length " . strlen($page_content) . ">>\nstream\n" . $page_content . "endstream";
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';
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
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref_offset . "\n%%EOF";
        return $pdf;
    }
}
