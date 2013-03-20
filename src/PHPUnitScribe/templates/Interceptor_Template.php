<?php
/**
 * Template file for generating the interceptor control structures
 */
class Interceptor_Template
{

    public function interceptor()
    {
        list($choice, $replacement) = PHPUnitScribe_Interceptor::intercept('__statement__');
        if ($choice === PHPUnitScribe_MockingChoice_Into) {
            __statement__;
        } else if ($choice === PHPUnitScribe_MockingChoice_Over) {
            PHPUnitScribe_Interceptor::disable();
            __statement__;
            PHPUnitScribe_Interceptor::enable();
        } else if ($choice === PHPUnitScribe_MockingChoice_Replace) {
            $__var__ = $replacement;
        }
    }

}
