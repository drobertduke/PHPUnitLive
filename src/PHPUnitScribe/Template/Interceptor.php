<?php
/**
 * Template file for generating the interceptor control structures
 */
class PHPUnitScribe_Template_Interceptor
{

    public function assignment_interceptor()
    {
        $PHPUnitScribe_exprs_to_resolve = \PHPUnitScribe_Interceptor::get_unresolved_exprs('__statement_escaped__');
        $PHPUnitScribe_resolved_exprs = array();
        foreach ($PHPUnitScribe_exprs_to_resolve as $PHPUnitScribe_expr_to_resolve)
        {
            $PHPUnitScribe_resolved_exprs[] = eval($PHPUnitScribe_expr_to_resolve);
        }
        $PHPUnitScribe_statement = \PHPUnitScribe_Interceptor::replace_unresolved_exprs('__statement_escaped__', $PHPUnitScribe_resolved_exprs);
        list($PHPUnitScribe_choice, $PHPUnitScribe_replacement) = \PHPUnitScribe_Interceptor::intercept($PHPUnitScribe_statement, true);
        if ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Into) {
            eval($PHPUnitScribe_statement . ';');
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Over) {
            \PHPUnitScribe_Interceptor::disable();
            eval($PHPUnitScribe_statement . ';');
            \PHPUnitScribe_Interceptor::enable();
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Replace) {
            __original_var__ = $PHPUnitScribe_replacement;
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_PrematureReturn) {
            return $PHPUnitScribe_replacement;
        } else {
            throw new Exception('no choice made');
        }
    }

    public function non_assignment_interceptor()
    {
        $PHPUnitScribe_exprs_to_resolve = \PHPUnitScribe_Interceptor::get_unresolved_exprs('__statement_escaped__');
        $PHPUnitScribe_resolved_exprs = array();
        foreach ($PHPUnitScribe_exprs_to_resolve as $PHPUnitScribe_expr_to_resolve)
        {
            $PHPUnitScribe_resolved_exprs[] = eval($PHPUnitScribe_expr_to_resolve);
        }
        $PHPUnitScribe_statement = \PHPUnitScribe_Interceptor::replace_unresolved_exprs('__statement_escaped__', $PHPUnitScribe_resolved_exprs);
        list($PHPUnitScribe_choice, $PHPUnitScribe_replacement) = \PHPUnitScribe_Interceptor::intercept('__statement_escaped__', false);
        if ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Into) {
            eval($PHPUnitScribe_statement . ';');
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Over) {
            \PHPUnitScribe_Interceptor::disable();
            eval($PHPUnitScribe_statement . ';');
            \PHPUnitScribe_Interceptor::enable();
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_PrematureReturn) {
            return $PHPUnitScribe_replacement;
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Suppress) {
            // No-op
        } else {
            throw new Exception('no choice made');
        }
    }
}
