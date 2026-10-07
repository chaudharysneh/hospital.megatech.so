<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$config['ci_balance_group']            = 'Balance Master';
$config['ci_balance_type']             = 'Previous Session Balance';
$config['ci_blog_theme']               = 'default';
$config['ci_front_page_url']           = 'page/';
$config['ci_front_page_read_url']      = 'read/';
$config['ci_front_event_content']      = 'events';
$config['ci_front_notice_content']     = 'notice';
$config['ci_front_gallery_content']    = 'gallery';
$config['ci_front_banner_content']     = 'banner';
$config['ci_front_home_page_slug']     = 'home';
$config['ci_front_complain_page_slug'] = 'complain';

$config['ci_front_themes'] = array(
    'atrium' => array(
        'name'    => 'Atrium',
        'preview' => 'theme_atrium.jpg',
        'colors'  => array(
            'aurora'  => '#0BAA8E',
            'saffron' => '#C97B1F',
            'royal'   => '#5746E2',
            'onyx'    => '#0B0F12',
        ),
    ),
    'organic' => array(
        'name'    => 'Organic',
        'preview' => 'theme_organic.jpg',
        'colors'  => array(
            'garden'   => '#3F5040',
            'pebble'   => '#3A4540',
            'sky'      => '#1E4549',
            'midnight' => '#15191A',
        ),
    ),
    'turquoise_blue_old' => array('name' => 'Turquoise (legacy)', 'preview' => 'theme_default.jpg',    'colors' => array()),
    'sky_blue_old'       => array('name' => 'Sky Blue (legacy)',  'preview' => 'theme_yellow.jpg',     'colors' => array()),
    'material_pink_old'  => array('name' => 'Material (legacy)',  'preview' => 'theme_darkgray.jpg',   'colors' => array()),
    'white_gray_old'     => array('name' => 'White Gray (legacy)','preview' => 'theme_white_gray.jpg', 'colors' => array()),
);
