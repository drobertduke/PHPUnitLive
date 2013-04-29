<?php
/**
 * Template file for generating the interceptor control structures
 */
class PHPUnitScribe_Template_Interceptor
{

    public function assignment_interceptor()
    {
        list($choice, $replacement) = PHPUnitScribe_Interceptor::intercept('__statement_escaped__');
        if ($choice === PHPUnitScribe_MockingChoice_Into) {
            __statement__;
        } elseif ($choice === PHPUnitScribe_MockingChoice_Over) {
            PHPUnitScribe_Interceptor::disable();
            __statement__;
            PHPUnitScribe_Interceptor::enable();
        } elseif ($choice === PHPUnitScribe_MockingChoice_Replace) {
            $__var__ = $replacement;
        }
    }

    public function non_assignment_interceptor()
    {
        list($choice, $replacement) = PHPUnitScribe_Interceptor::intercept('__statement_escaped__');
        if ($choice === PHPUnitScribe_MockingChoice_Into) {
            __statement__;
        } elseif ($choice === PHPUnitScribe_MockingChoice_Over) {
            PHPUnitScribe_Interceptor::disable();
            __statement__;
            PHPUnitScribe_Interceptor::enable();
        }
    }
}
