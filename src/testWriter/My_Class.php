<?php

class My_Class {

	public function __construct() {
		echo 'constructing';
	}
	public function short_format($str) {
		echo "short format $str\n";
	}

	public function long_format($str) {
		echo "long format $str\n";
	}
}
