#! /usr/bin/php
<?php
require_once('autoloader.php');
require_once('../vendor/PHP-Parser/lib/bootstrap.php');

$file_name = $argv[1];
$test_name = $argv[2];
$fast_forward = $argv[3];
$remaining_statements_to_read = $argv[4];

$editor = new PHPUnitScribe_TestEditor($file_name, $test_name, $remaining_statements_to_read);

$should_fast_forward = ($fast_forward == '1');
$editor->execute($should_fast_forward);
