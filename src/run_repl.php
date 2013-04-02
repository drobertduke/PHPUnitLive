#! /usr/bin/php
<?php
require_once('autoloader.php');
require_once('../vendor/PHP-Parser/lib/bootstrap.php');
$file_name = $argv[1];
$test_name = $argv[2];
$fast_forward = $argv[3];

$editor = new PHPUnitScribe_TestEditor($file_name, $test_name, $fast_forward);
$editor->execute(false);
