<?php
/**
 */
class Decomposer_Test_Class
{
    protected function protected_thing($things)
    {
        foreach($things as $thing)
        {
            echo $thing;
        }
    }
    public function do_a_thing()
    {
        $this->protected_thing($this->generate_things());
        $this->{'p' . $this->funcName()}();
        $this->get_obj()->protected_thing(array('wow'));
        $r = 'qwe';
        $this->nothing($r);
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
