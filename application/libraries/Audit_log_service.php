<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_log_service
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();

        $this->CI->load->model(
            'system/Audit_log_model'
        );

        $this->CI->load->library('session');
    }

    public function log(
        $module,
        $action,
        $record_id = null,
        $description = null,
        $details = null
    ) {
        $data = [
            'user_id' =>
                $this->CI
                    ->session
                    ->userdata('user_id'),

            'employee_no' =>
                $this->CI
                    ->session
                    ->userdata('employee_no'),

            'session_id' =>
                $this->CI
                    ->session
                    ->userdata('session_token'),

            'module' =>
                $module,

            'action' =>
                $action,

            'record_id' =>
                $record_id,

            'description' =>
                $description,

            'details' =>
                $details !== null
                    ? json_encode($details)
                    : null,

            'ip_address' =>
                $this->CI
                    ->input
                    ->ip_address(),

            'user_agent' =>
                $this->CI
                    ->input
                    ->user_agent()
        ];

        return $this->CI
            ->Audit_log_model
            ->create($data);
    }


    public function get_changed_fields(
        $old,
        $new
    ) {
        $changes = [];

        if (!$old || !$new) {
            return $changes;
        }

        $ignored_fields = [
            'id',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'deleted_at'
        ];

        if (is_object($new)) {
            $new = (array) $new;
        }

        foreach ($new as $field => $new_value) {

            if (
                in_array(
                    $field,
                    $ignored_fields,
                    true
                )
            ) {
                continue;
            }

            $old_value =
                $old->$field ?? null;

            if (
                (string) $old_value !==
                (string) $new_value
            ) {
                $changes[$field] = [
                    'old' => $old_value,
                    'new' => $new_value
                ];
            }
        }

        return $changes;
    }


    public function get_changed_records(
        $old_records,
        $new_records
    ) {
        $changes = [];

        $old_by_id = [];

        foreach ($old_records as $record) {

            $old_by_id[(int) $record->id] =
                $record;
        }

        $new_by_id = [];

        foreach ($new_records as $record) {

            $new_by_id[(int) $record->id] =
                $record;
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE / DELETE
        |--------------------------------------------------------------------------
        */

        foreach ($old_by_id as $id => $old) {

            if (isset($new_by_id[$id])) {

                $field_changes =
                    $this->get_changed_fields(
                        $old,
                        $new_by_id[$id]
                    );

                if (!empty($field_changes)) {

                    $changes[$id] = [
                        'action' => 'UPDATE',
                        'changes' => $field_changes
                    ];
                }

            } else {

                $changes[$id] = [
                    'action' => 'DELETE',
                    'old' => (array) $old
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        */

        foreach ($new_by_id as $id => $new) {

            if (!isset($old_by_id[$id])) {

                $changes[$id] = [
                    'action' => 'CREATE',
                    'new' => (array) $new
                ];
            }
        }

        return $changes;
    }


    // private function get_changed_records(
    //     $old_records,
    //     $new_records
    // ) {
    //     $changes = [];

    //     $old_by_id = [];

    //     foreach ($old_records as $record) {
    //         $old_by_id[$record->id] = $record;
    //     }

    //     $new_by_id = [];

    //     foreach ($new_records as $record) {
    //         $new_by_id[$record->id] = $record;
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Updated / Deleted
    //     |--------------------------------------------------------------------------
    //     */

    //     foreach ($old_by_id as $id => $old) {

    //         if (isset($new_by_id[$id])) {

    //             $field_changes =
    //                 $this->get_changed_fields(
    //                     $old,
    //                     $new_by_id[$id]
    //                 );

    //             if (!empty($field_changes)) {
    //                 $changes[$id] = [
    //                     'action' => 'UPDATE',
    //                     'changes' => $field_changes
    //                 ];
    //             }

    //         } else {

    //             $changes[$id] = [
    //                 'action' => 'DELETE',
    //                 'old' => (array) $old
    //             ];
    //         }
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Created
    //     |--------------------------------------------------------------------------
    //     */

    //     foreach ($new_by_id as $id => $new) {

    //         if (!isset($old_by_id[$id])) {

    //             $changes[$id] = [
    //                 'action' => 'CREATE',
    //                 'new' => (array) $new
    //             ];
    //         }
    //     }

    //     return $changes;
    // }

    // private function get_changed_fields(
    //     $old,
    //     $new
    // ) {
    //     $changes = [];

    //     $ignored_fields = [
    //         'id',
    //         'created_at',
    //         'created_by',
    //         'updated_at',
    //         'updated_by',
    //         'deleted_at'
    //     ];

    //     if (is_object($new)) {
    //         $new = (array) $new;
    //     }

    //     foreach ($new as $field => $new_value) {

    //         if (
    //             in_array(
    //                 $field,
    //                 $ignored_fields,
    //                 true
    //             )
    //         ) {
    //             continue;
    //         }

    //         $old_value =
    //             $old->$field ?? null;

    //         if (
    //             (string) $old_value !==
    //             (string) $new_value
    //         ) {
    //             $changes[$field] = [
    //                 'old' => $old_value,
    //                 'new' => $new_value
    //             ];
    //         }
    //     }

    //     return $changes;
    // }
}