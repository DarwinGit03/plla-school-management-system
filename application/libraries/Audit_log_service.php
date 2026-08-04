<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_log_service
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function log(
        $user_id,
        $module,
        $action,
        $description = null
    )
    {
        return $this->CI
                    ->db
                    ->insert(
                        'audit_logs',
                        [
                            'user_id' =>
                                $user_id,
                            'module' =>
                                $module,
                            'action' =>
                                $action,
                            'description' =>
                                $description,
                            'ip_address' =>
                                $this->CI
                                    ->input
                                    ->ip_address(),
                            // 'user_agent' =>
                            //     $this->CI
                            //         ->input
                            //         ->user_agent(),
                            'created_at' =>
                                date(
                                    'Y-m-d H:i:s'
                                )

                        ]
                    );
    }
}