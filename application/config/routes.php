<?php

defined('BASEPATH') or exit('No direct script access allowed');


function my_autoloader($class)
{

    if (substr($class, 0, 9) == "MY_Addon_") {
   
       if (file_exists($file = APPPATH . 'core/' . $class . '.php')) {
            include $file;
        }
    }
}
spl_autoload_register('my_autoloader');

$route['default_controller']                 = 'welcome/index';
$route['user/resetpassword/([a-z]+)/(:any)'] = 'site/resetpassword/$1/$2';
$route['admin/resetpassword/(:any)']         = 'site/admin_resetpassword/$1';
$route['admin/unauthorized']                 = 'admin/admin/unauthorized';
$route['404_override'] = 'welcome/show_404';
$route['translate_uri_dashes'] = false;
$route['form/appointment']     = 'welcome/appointment';
$route['page/annual_calendar']     = 'welcome/annual_calendar';

//======= front url rewriting==========
$route['page/(:any)'] = 'welcome/page/$1';
$route['read/(:any)'] = 'welcome/read/$1';
$route['frontend']    = 'welcome';

//======= Survey Forms — Public listing page ==========
$route['surveys'] = 'welcome/survey_forms';

//======= Survey Forms — Themed fill & success (renders inside front theme) ==========
$route['surveys/fill/(:any)']    = 'welcome/form_fill/$1';
$route['surveys/success/(:num)'] = 'welcome/form_success/$1';

//======= Survey Form — Public (no auth) ==========
$route['surveyform/fill/(:any)']    = 'surveyform/fill/$1';
$route['surveyform/submit']         = 'surveyform/submit';
$route['surveyform/success/(:num)'] = 'surveyform/success/$1';
