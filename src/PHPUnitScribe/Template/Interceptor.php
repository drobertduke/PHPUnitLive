<?php
/**
 * Template file for generating the interceptor control structures
 */
class PHPUnitScribe_Template_Interceptor
{

    public function assignment_interceptor()
    {
        list($PHPUnitScribe_choice, $PHPUnitScribe_replacement) = \PHPUnitScribe_Interceptor::intercept('__statement_escaped__', true);
        if ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Into) {
            __statement__;
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Over) {
            \PHPUnitScribe_Interceptor::disable();
            __statement__;
            \PHPUnitScribe_Interceptor::enable();
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Replace) {
            __replacement_statement__;
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_PrematureReturn) {
            __return_statement__;
        } else {
            throw new Exception('no choice made');
        }
    }

    public function non_assignment_interceptor()
    {
        list($PHPUnitScribe_choice, $PHPUnitScribe_garbage) = \PHPUnitScribe_Interceptor::intercept('__statement_escaped__', false);
        if ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Into) {
            __statement__;
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Over) {
            \PHPUnitScribe_Interceptor::disable();
            __statement__;
            \PHPUnitScribe_Interceptor::enable();
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_PrematureReturn) {
            __return_statement__;
        } else {
            throw new Exception('no choice made');
        }
    }
}
