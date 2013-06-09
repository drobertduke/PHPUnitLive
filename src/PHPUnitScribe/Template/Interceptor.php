<?php
/**
 * Template file for generating the interceptor control structures
 */
class PHPUnitScribe_Template_Interceptor
{

    public function assignment_interceptor()
    {
        $PHPUnitScribe_exprs_to_resolve = \PHPUnitScribe_Interceptor::get_unresolved_exprs_from_string('__statement_escaped__');
        $PHPUnitScribe_resolved_exprs = array();
        foreach ($PHPUnitScribe_exprs_to_resolve as $part_name => $PHPUnitScribe_expr_to_resolve)
        {
            $PHPUnitScribe_resolved_exprs[$part_name] = eval('return ' . $PHPUnitScribe_expr_to_resolve);
        }
        list($PHPUnitScribe_object_types, $PHPUnitScribe_statement_string) =
            \PHPUnitScribe_Interceptor::replace_unresolved_exprs('__statement_escaped__', $PHPUnitScribe_resolved_exprs);
        list($PHPUnitScribe_choice, $PHPUnitScribe_replacement) =
            \PHPUnitScribe_Interceptor::intercept($PHPUnitScribe_statement_string, $PHPUnitScribe_object_types, true, '__statement_escaped__');
        if ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Into) {
            eval('__statement_escaped__');
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Over) {
            \PHPUnitScribe_Interceptor::disable();
            eval('__statement_escaped__');
            \PHPUnitScribe_Interceptor::enable();
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Replace) {
            __replacement_statement__;
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_PrematureReturn) {
            return $PHPUnitScribe_replacement;
        } else {
            throw new Exception('no choice made -- ' . $PHPUnitScribe_choice);
        }
    }

    public function non_assignment_interceptor()
    {
        $PHPUnitScribe_exprs_to_resolve = \PHPUnitScribe_Interceptor::get_unresolved_exprs_from_string('__statement_escaped__');
        $PHPUnitScribe_resolved_exprs = array();
        foreach ($PHPUnitScribe_exprs_to_resolve as $part_name => $PHPUnitScribe_expr_to_resolve)
        {
            $PHPUnitScribe_resolved_exprs[$part_name] = eval('return ' . $PHPUnitScribe_expr_to_resolve);
        }
        list($PHPUnitScribe_object_types, $PHPUnitScribe_statement_string) =
            \PHPUnitScribe_Interceptor::replace_unresolved_exprs('__statement_escaped__', $PHPUnitScribe_resolved_exprs);
        list($PHPUnitScribe_choice, $PHPUnitScribe_replacement) =
            \PHPUnitScribe_Interceptor::intercept($PHPUnitScribe_statement_string, $PHPUnitScribe_object_types, false, '__statement_escaped__');
        if ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Into) {
            eval('__statement_escaped__');
        } elseif ($PHPUnitScribe_choice === PHPUnitScribe_InterceptionChoice_Over) {
            \PHPUnitScribe_Interceptor::disable();
            eval('__statement_escaped__');
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
