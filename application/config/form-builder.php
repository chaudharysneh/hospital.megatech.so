<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$config['contact_us'] = array(
    'email_title' => array( /* HIDDEN */
        'id'            => 'email_title',
        'type'          => 'hidden',
        'value'         => 'New Inquiry From Contact US',
        'mail_response' => 'We will contact you soon.',
    ),
    'name'        => array(
        'id'          => 'name',
        'placeholder' => lang('enter_your_name'),
        'validation'  => 'trim|required|xss_clean',
    ),
    'email'       => array(
        'id'          => 'email',
        'type'        => 'email',
        'placeholder' => lang('enter_your_email'),
        'validation'  => 'trim|required|valid_email|xss_clean',
    ),
    'subject'     => array(
        'id'          => 'subject',
        'placeholder' => lang('enter_subject'),
        'validation'  => 'trim|required|xss_clean',
    ),
    'description' => array( /* TEXTAREA */
        'id'          => 'description',
        'type'        => 'textarea',
        'placeholder' => lang('enter_description'),
    ),
    'submit'      => array( /* SUBMIT */
        'id'    => 'submit',
        'type'  => 'submit',
        'class' => 'view-all-btn',
    ),
);

$config['complain'] = array(
    'email_title' => array( /* HIDDEN */
        'id'            => 'email_title',
        'type'          => 'hidden',
        'value'         => 'New Inquiry From Complain',
        'mail_response' => 'Thank you for your complain.',
    ),
    'name'        => array(
        'id'          => 'name',
        'placeholder' => lang('enter_your_name'),
        'validation'  => 'trim|required|xss_clean',
    ),
    'email'       => array(
        'id'          => 'email',
        'type'        => 'email',
        'placeholder' => lang('enter_your_email'),
        'validation'  => 'trim|required|valid_email|xss_clean',
    ),
    'contact_no'  => array(
        'id'          => 'contact',
        'placeholder' => lang('enter_your_contact_no'),
        'validation'  => 'trim|required|xss_clean',
    ),
    'description' => array( /* TEXTAREA */
        'id'          => 'description',
        'type'        => 'textarea',
        'placeholder' => lang('enter_description'),
    ),
    'submit'      => array( /* SUBMIT */
        'id'    => 'submit',
        'type'  => 'submit',
        'class' => 'view-all-btn',
    ),
);
