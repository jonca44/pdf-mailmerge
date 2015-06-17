<?php
/*
 * jQuery File Upload Plugin PHP Example 5.2.9.subfolders
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://creativecommons.org/licenses/MIT/
 */

error_reporting(E_ALL | E_STRICT);

require("../../php/sessionStartAndCheck.php");
require('upload.class.php');

$options = array(
            'script_url' => $_SERVER['PHP_SELF'],
            'upload_dir' => dirname(__FILE__).'/user_files/'.$session->get_user_var('file_directory').'/datasources/',
            'upload_url' => dirname($_SERVER['PHP_SELF']).'/user_files/'.$session->get_user_var('file_directory').'/datasources/',
            'param_name' => 'files',
            'accept_file_types' => '/\.(csv)$/i',
            'image_versions' => array()
        );
		
$upload_handler = new UploadHandler($options, 1);

header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'HEAD':
    case 'GET':
        $upload_handler->get();
        break;
    case 'POST':
        $upload_handler->post();
        break; 
    case 'DELETE':
        $upload_handler->delete();
        break;
    case 'OPTIONS':
        break;
    default:
        header('HTTP/1.0 405 Method Not Allowed');
}

//$sql->close();
?>