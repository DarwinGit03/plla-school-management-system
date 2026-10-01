<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('has_permission')) {
    function has_permission($permission)
    {
        static $permissions_by_user = [];

        $CI =& get_instance();
        $user_id = (int) $CI->session->userdata('user_id');
        if (!isset($permissions_by_user[$user_id])) {
            $CI->load->model('rbac/Permission_model', 'permission_model');
            $permissions_by_user[$user_id] = $CI->permission_model->get_effective_permissions($user_id);
        }

        return isset($permissions_by_user[$user_id][(string) $permission]);
    }
}
