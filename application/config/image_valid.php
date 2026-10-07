<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$config['adm_digit_length'] = 6;

$config['image_validate'] = array(
    'allowed_mime_type' => array('image/jpeg', 'image/jpg', 'image/png'), //mime_type
    'allowed_extension' => array('jpg', 'jpeg', 'png'), // image extensions
    'upload_size'       => 1048576000, // bytes
);

$config['file_validate'] = array(
    'allowed_mime_type' => array(
        'image/jpeg',
        'image/jpg',
        'image/png',

        // PDF
        'application/pdf',

        // Word
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

        // Excel
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

        // CSV (multiple MIME support)
        'text/csv',
        'text/plain',
        'application/csv',
        'application/vnd.ms-excel',

        // ZIP
        'application/zip',
        'application/x-zip-compressed',
        'multipart/x-zip'
    ),

    'allowed_extension' => array(
        'jpg',
        'jpeg',
        'png',
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',

        // CSV
        'csv',

        // ZIP
        'zip'
    ),

    'upload_size' => 104857600, // bytes (100 MB)
);

$config['filecsv_validate'] = array(
    'allowed_mime_type' => array('text/csv', 'application/vnd.ms-excel', 'application/octet-stream'), //mime_type
    'allowed_extension' => array('csv', 'xls'), // image extensions
    'upload_size'       => 1048576000, // bytes
);
