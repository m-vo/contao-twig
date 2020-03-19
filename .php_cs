<?php
$finder = PhpCsFixer\Finder::create()->in([
    __DIR__ . '/src',
    __DIR__ . '/tests',
]);

$header = <<<EOF
@author  Moritz Vondano
@license MIT
EOF;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@PHP71Migration' => true,
            '@PHP71Migration:risky' => true,
            '@PHPUnit60Migration:risky' => true,
            '@Symfony' => true,
            '@Symfony:risky' => true,
            'align_multiline_comment' => true,
            'array_syntax' => ['syntax' => 'short'],
            'blank_line_before_statement' => [
                'statements' => [
                    'case',
                    'declare',
                    'default',
                    'do',
                    'for',
                    'foreach',
                    'if',
                    'return',
                    'switch',
                    'throw',
                    'try',
                    'while',
                ],
            ],
            'combine_consecutive_unsets' => true,
            'compact_nullable_typehint' => true,
            'declare_strict_types' => true,
            'header_comment' => ['header' => $header],
            'heredoc_to_nowdoc' => true,
            'list_syntax' => ['syntax' => 'short'],
            'no_extra_blank_lines' => [
                'tokens' => [
                    'curly_brace_block',
                    'extra',
                    'parenthesis_brace_block',
                    'square_brace_block',
                    'throw',
                    'use',
                ]
            ],
            'no_superfluous_phpdoc_tags' => true,
            'no_unreachable_default_argument_value' => true,
            'no_useless_else' => true,
            'no_useless_return' => true,
            'ordered_class_elements' => true,
            'ordered_imports' => true,
            'php_unit_strict' => true,
            'phpdoc_add_missing_param_annotation' => true,
            'phpdoc_order' => true,
            'psr4' => true,
            'static_lambda' => true,
            'strict_comparison' => true,
            'strict_param' => true,
        ]
    )
    ->setFinder($finder);
