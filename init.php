<?php
session_start();
session_regenerate_id(true);

spl_autoload_register(function ($class_name) {
    $path = __DIR__ . "/classes/";
    $theClass = "{$path}{$class_name}.php";
    if (file_exists($theClass)) return require_once $theClass;
    exit("Error: The \"{$class_name}\" class not found in {$path} || Or check your spelling.");
});
