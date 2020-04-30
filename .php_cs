<?php

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['align_double_arrow' => false, 'align_equals' => false],
        'blank_line_after_opening_tag' => true,
        'blank_line_before_return' => true,
        'cast_spaces' => true,
        'concat_space' => ['spacing' => 'none'],
        'declare_strict_types' => true,
        'fully_qualified_strict_types' => true,
        'method_separation' => true,
        'native_function_invocation' => true,
        'new_with_braces' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_extra_blank_lines' => true,
        'no_spaces_around_offset' => ['positions' => ['inside', 'outside']],
        'no_unneeded_control_parentheses' => true,
        'no_unused_imports' => true,
        'no_whitespace_in_blank_line' => true,
        'ordered_imports' => true,
        'phpdoc_align' => true,
        'phpdoc_no_access' => true,
        'phpdoc_separation' => true,
        'php_unit_fqcn_annotation' => true,
        'pre_increment' => true,
        'self_accessor' => true,
        'single_blank_line_before_namespace' => true,
        'single_quote' => true,
        'return_type_declaration' => true,
        'trailing_comma_in_multiline_array' => true,
        'trim_array_spaces' => true,
        'void_return' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tests')
            ->in(__DIR__ . '/benchmarks')
    )
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
;
