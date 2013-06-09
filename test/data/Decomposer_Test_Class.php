<?php
/**
 */

function outside_function($arg1)
{
    echo $arg1;
}

class Decomposer_Test_Class
{
    protected function protected_thing($things)
    {
        foreach($things as $thing)
        {
            echo $thing;
        }
    }
    protected function return_two_things()
    {
        return array(0, 1);
    }
    public function do_a_thing()
    {
        include_once "/Users/davidlcg/projs/PHPUnitScribe/src/PHPUnitScribe/InterceptionChoice.php";
        $this->protected_thing($this->generate_things());
        $this->{'p' . $this->funcName()}();
        $this->get_obj()->protected_thing(array('wow'));
        $test_class = $this;
        echo $this->takes_fn($this->funcName(), function($arg_a) use ($test_class)
        {
            $test_class->pThing();
            $test_class->nothing($this->return_a());
            return $arg_a;
        });
        $g = $this->get_obj();
        $r = 'qwe';
        if ($q = $this->funcName())
        {
            $k = $this->return_a();
        }
        $this->nothing($r);
        list($a, $b) = $this->return_two_things();
        $func_to_call = 'outside_function';
        $func_to_call('hi');
    }

    protected function return_a()
    {
        return 'a';
    }

    protected function takes_fn($str, $fn)
    {
        echo $str;
        return $fn($str);
    }

    protected function get_obj()
    {
        return $this;
    }

    protected function nothing($arg)
    {
        echo $arg;
    }

    protected function pThing()
    {
        echo "pThing\n";
    }

    protected function funcName()
    {
        return "Thing";
    }

    protected function generate_things()
    {
        return array(1,2,3,4);
    }

}
