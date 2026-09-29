<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Validates finance payment input before delegating persistence to the model. */
class Payment_service
{
    protected $CI;

    protected $payment_methods = [
        'cash',
        'bank_transfer',
        'card',
        'gcash',
        'maya',
        'check',
        'other'
    ];

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('finance/payment_model');
    }

    public function get_payment_methods()
    {
        return [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank transfer',
            'card' => 'Card',
            'gcash' => 'GCash',
            'maya' => 'Maya',
            'check' => 'Check',
            'other' => 'Other'
        ];
    }

    public function record_payment(array $input, $recorded_by)
    {
        $fee_id = (int) ($input['enrollment_fee_id'] ?? 0);
        $student_id = (int) ($input['student_id'] ?? 0);
        $amount = trim((string) ($input['amount_paid'] ?? ''));
        $method = $this->normalize_payment_method($input['payment_method'] ?? '');
        $reference = trim((string) ($input['reference_number'] ?? ''));

        if ($fee_id < 1 || $student_id < 1 || !is_numeric($amount) || (float) $amount <= 0) {
            return ['ok' => false, 'message' => 'Enter a valid payment amount.'];
        }

        if (!in_array($method, $this->payment_methods, true)) {
            return ['ok' => false, 'message' => 'Choose a valid payment method.'];
        }

        if (strlen($reference) > 100) {
            return ['ok' => false, 'message' => 'Reference number must be 100 characters or fewer.'];
        }

        $result = $this->CI->payment_model->record_payment(
            $fee_id,
            $student_id,
            $amount,
            $method,
            $reference,
            $recorded_by
        );

        if (!$result['ok']) {
            $messages = [
                'invalid_request' => 'The payment request is invalid. Refresh the page and try again.',
                'assessment_not_found' => 'The selected fee assessment could not be found.',
                'invalid_amount' => 'Enter a payment amount greater than zero.',
                'invalid_payment_method' => 'Choose a valid payment method.',
                'invalid_reference' => 'Reference number must be 100 characters or fewer.',
                'amount_exceeds_balance' => 'The payment exceeds the remaining balance.',
                'save_failed' => 'The payment could not be saved.'
            ];

            return [
                'ok' => false,
                'message' => $messages[$result['code']] ?? $messages['save_failed']
            ];
        }

        return [
            'ok' => true,
            'payment_id' => $result['payment_id'],
            'message' => 'Payment saved successfully.'
        ];
    }

    public function void_payment($payment_id, $student_id, $reason, $performed_by)
    {
        $reason = trim((string) $reason);
        if ((int) $payment_id < 1 || (int) $student_id < 1) {
            return ['ok' => false, 'message' => 'The payment record could not be identified. Refresh the page and try again.'];
        }
        if ($this->character_length($reason) < 5 || $this->character_length($reason) > 500) {
            return ['ok' => false, 'message' => 'Enter a reason between 5 and 500 characters.'];
        }
        $result = $this->CI->payment_model->void_payment(
            (int) $payment_id,
            (int) $student_id,
            $reason,
            $performed_by
        );
        if (!$result['ok']) {
            $messages = [
                'invalid_request' => 'The payment record could not be identified. Refresh the page and try again.',
                'invalid_reason' => 'Enter a reason between 5 and 500 characters.',
                'payment_not_found' => 'The selected payment could not be found for this student.',
                'payment_not_posted' => 'Only a posted payment can be voided.',
                'save_failed' => 'The payment could not be voided. No changes were saved.'
            ];
            return ['ok' => false, 'message' => $messages[$result['code']] ?? 'The payment could not be voided.'];
        }
        return ['ok' => true, 'message' => 'Payment voided. The original receipt remains in the history.'];
    }

    public function correct_payment_details($payment_id, $student_id, array $input, $performed_by)
    {
        $method = $this->normalize_payment_method($input['payment_method'] ?? '');
        $reference = trim((string) ($input['reference_number'] ?? ''));
        $reason = trim((string) ($input['reason'] ?? ''));
        if ((int) $payment_id < 1 || (int) $student_id < 1) {
            return ['ok' => false, 'message' => 'The payment record could not be identified. Refresh the page and try again.'];
        }
        if (!in_array($method, $this->payment_methods, true)) {
            return ['ok' => false, 'message' => 'Choose a valid payment method.'];
        }
        if (strlen($reference) > 100) return ['ok' => false, 'message' => 'Reference number must be 100 characters or fewer.'];
        if ($this->character_length($reason) < 5 || $this->character_length($reason) > 500) return ['ok' => false, 'message' => 'Enter a reason between 5 and 500 characters.'];

        $result = $this->CI->payment_model->correct_payment_details(
            (int) $payment_id,
            (int) $student_id,
            $method,
            $reference,
            $reason,
            $performed_by
        );
        if (!$result['ok']) {
            $messages = [
                'invalid_request' => 'The payment record could not be identified. Refresh the page and try again.',
                'invalid_payment_method' => 'Choose a valid payment method.',
                'invalid_reference' => 'Reference number must be 100 characters or fewer.',
                'invalid_reason' => 'Enter a reason between 5 and 500 characters.',
                'payment_not_found' => 'The selected payment could not be found for this student.',
                'payment_not_posted' => 'Only a posted payment can be corrected.',
                'no_changes' => 'No payment details were changed.',
                'save_failed' => 'The correction could not be saved. No changes were applied.'
            ];
            return ['ok' => false, 'message' => $messages[$result['code']] ?? 'The correction could not be saved.'];
        }
        return ['ok' => true, 'message' => 'Payment details corrected and recorded in the audit history.'];
    }

    /** Normalize older display labels while still accepting only known methods. */
    private function normalize_payment_method($method)
    {
        $method = strtolower(trim((string) $method));
        $method = str_replace([' ', '-'], '_', $method);
        $aliases = [
            'banktransfer' => 'bank_transfer',
            'g_cash' => 'gcash'
        ];
        return $aliases[$method] ?? $method;
    }

    private function character_length($value)
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
