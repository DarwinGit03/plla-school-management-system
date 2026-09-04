<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
// $route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

//set in controllers

$route['default_controller'] = 'auth'; //pages
$route['login'] = 'auth';//pages
$route['auth/login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['forgot-password'] = 'auth/forgot_password'; //pages
$route['verify-otp-page'] = 'auth/verify_otp_page'; //pages
$route['auth/verify_otp'] = 'auth/verify_otp';

$route['reset-password'] = 'auth/reset_password'; //pages
$route['reset-password-page'] = 'auth/reset_password_page'; //pages
$route['dashboard'] = 'dashboard';

$route['send-otp'] = 'auth/send_otp';
$route['check-otp'] = 'auth/check_otp';
$route['auth/save_password'] = 'auth/save_password';


$route['dashboard'] = 'dashboard/index';
$route['students'] = 'students/index';

//admin 
$route['admin/unlock-account/(:num)'] = 'auth/unlockAccount/$1';

//security
$route['keep-alive'] = 'auth/keepAlive';

$route['students/check-lrn'] = 'students/check_lrn';

//Student update status
$route['students/change-status/(:num)'] =
    'students/change_status/$1';