<?php
/**
 * Created by JetBrains PhpStorm.
 * User: davidlcg
 * Date: 4/24/13
 * Time: 8:50 AM
 * To change this template use File | Settings | File Templates.
 */
class Decomposer_Simple_Class
{
    public function do_a_simple_thing()
    {
        $this->private_thing($this->property);
        $this->{'p' . $this->funcName()}();
    }
}
