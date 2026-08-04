<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Require Login
|--------------------------------------------------------------------------
*/

function require_login()
{
    $CI =& get_instance();

    if(
        !$CI->session->userdata('logged_in')
    )
    {
        redirect('login');
    }
}

/*
|--------------------------------------------------------------------------
| Guest Only
|--------------------------------------------------------------------------
*/

// function guest_only()
// {
//     $CI =& get_instance();

//     if(
//         $CI->session->userdata('logged_in')
//     )
//     {
//         redirect('dashboard');
//     }
// }
function guest_only()
{
    if(get_instance()->session->userdata('user_id'))
    {
        redirect('dashboard');
    }
}
/*
|--------------------------------------------------------------------------
| Current User ID
|--------------------------------------------------------------------------
*/

function current_user_id()
{
    $CI =& get_instance();

    return
        $CI->session
           ->userdata(
                'user_id'
           );
}

/*
|--------------------------------------------------------------------------
| Current Role
|--------------------------------------------------------------------------
*/

function current_role()
{
    $CI =& get_instance();

    return
        $CI->session
           ->userdata(
                'role_id'
           );
}

/*
|--------------------------------------------------------------------------
| Is Administrator
|--------------------------------------------------------------------------
*/

function is_admin()
{
    return
        in_array(
            current_role(),
            [1,2]
        );
}

/*
|--------------------------------------------------------------------------
| Is Finance
|--------------------------------------------------------------------------
*/

function is_finance()
{
    return
        current_role() == 3;
}

/*
|--------------------------------------------------------------------------
| Is Teacher
|--------------------------------------------------------------------------
*/

function is_teacher()
{
    return
        current_role() == 4;
}

/*
|--------------------------------------------------------------------------
| Is Parent
|--------------------------------------------------------------------------
*/

function is_parent()
{
    return
        current_role() == 5;
}

/*
|--------------------------------------------------------------------------
| Require Roles
|--------------------------------------------------------------------------
*/

// function require_role($roles = [])
// {
//     $CI =& get_instance();

//     $current_role =
//         $CI->session->userdata(
//             'role_id'
//         );

//     if(
//         !in_array(
//             $current_role,
//             $roles
//         )
//     )
//     {
//         show_error(
//             'Access Denied.',
//             403
//         );
//     }
// }

/*
|--------------------------------------------------------------------------
| Require Role
|--------------------------------------------------------------------------
*/

function require_role($roles = [])
{
    $CI =& get_instance();

    $role_id =
        $CI->session->userdata(
            'role_id'
        );

    if(
        !in_array(
            $role_id,
            $roles
        )
    )
    {
        show_error(
            'Access Denied.',
            403
        );
    }
}