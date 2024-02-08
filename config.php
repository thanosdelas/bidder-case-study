<?php
//
// Define ENVIRNOMENT.
// Could be set according to domain.
// In that case it should fallback to production.
// Use the following to match current domain and define ENVIRNOMENT accordingly.
// strtolower(parse_url($_SERVER['HTTP_HOST'])['path']);
//
DEFINE('ENVIRNOMENT', 'development');

//
// Set config paramaters according to ENVIRNOMENT
//
switch (ENVIRNOMENT):

  case 'development':
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    DEFINE("SOURCE", dirname(__FILE__).'/src');

    $config['displayErrorDetails'] = true;
    $config['addContentLengthHeader'] = false;
    $config['db']['host']   = 'localhost';
    $config['db']['user']   = 'root';
    $config['db']['pass']   = '';
    $config['db']['dbname'] = 'testphp';
    break;

  case 'production':
    error_reporting(0);
    ini_set('display_errors', 0);

    DEFINE("SOURCE", dirname(__FILE__).'/src');

    $config['displayErrorDetails'] = false;
    $config['addContentLengthHeader'] = false;
    $config['db']['host']   = '';
    $config['db']['user']   = '';
    $config['db']['pass']   = '';
    $config['db']['dbname'] = '';
    break;

  default:
    exit("[!] Could not set environment");
    break;

endswitch;

//
// Throw exceptions on undefined variables insetad of stupid PHP notices/warnings.
//
set_error_handler(function($severity, $message, $file, $line){
  if (!(error_reporting() & $severity)) { return false; }
  throw new ErrorException($message, 0, $severity, $file, $line);
});
