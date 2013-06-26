<?php

define('BASE_PATH', dirname(__DIR__));
set_include_path(BASE_PATH . '/src' . PATH_SEPARATOR . BASE_PATH . '/tests/support' . PATH_SEPARATOR . get_include_path());

function loadClass($name)
{
    $filename = strtr($name, '\\_', '//') . ".php";
    if (@fopen($filename, 'r', true)) require_once $filename;
}

spl_autoload_register('loadClass');
