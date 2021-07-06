<?php

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['operators' => ['=>' => 'single_space', '=' => 'single_space']],
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => ['statements' => ['return']],
        'cast_spaces' => true,
        'concat_space' => ['spacing' => 'none'],
        'declare_strict_types' => true,
        'fully_qualified_strict_types' => true,
        'phpdoc_separation' => true,
        'native_function_invocation' => ['include' => ['@all']],
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
        'php_unit_fqcn_annotation' => true,
        'self_accessor' => true,
        'single_blank_line_before_namespace' => true,
        'single_quote' => true,
        'return_type_declaration' => true,
        'trailing_comma_in_multiline' => true,
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
