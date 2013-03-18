<?php

class My_Class {

	public function __construct() {
		echo 'constructing';
	}
	public function short_format($str) {
        $d = $this->unmockable($str);
		echo "short format $d\n";
	}

	public function long_format($str) {
		echo "long format $str\n";
	}

    private function unmockable($str) {
        echo "this function is unmackable $str\n";
        return 2;
    }
}
