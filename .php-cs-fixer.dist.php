<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()->in(__DIR__ . '/src');

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                                           => true,
        '@PhpCsFixer'                                      => true,
        '@PhpCsFixer:risky'                                => true,
        '@PHP80Migration:risky'                            => true,
        '@PHP81Migration'                                  => true,
        'binary_operator_spaces'                           => ['operators' => array_fill_keys(['=', '=>', '??=', '.=', '+=', '-=', '*=', '/=', '%=', '**=', '&=', '|=', '^=', '<<=', '>>='], 'align_single_space_minimal')],
        'blank_line_before_statement'                      => ['statements' => ['break', 'case', 'continue', 'declare', 'default', 'do', 'exit', 'for', 'foreach', 'goto', 'if', 'include', 'include_once', 'phpdoc', 'require', 'require_once', 'return', 'switch', 'throw', 'try', 'while', 'yield', 'yield_from']],
        'class_attributes_separation'                      => ['elements' => ['case' => 'none', 'method' => 'one', 'property' => 'one', 'trait_import' => 'none']],
        'class_definition'                                 => ['multi_line_extends_each_single_line' => true, 'single_item_single_line' => true],
        'concat_space'                                     => ['spacing' => 'one'],
        'date_time_create_from_format_call'                => true,
        'date_time_immutable'                              => true,
        'declare_strict_types'                             => true,
        'final_class'                                      => true,
        'final_public_method_for_abstract_class'           => true,
        'get_class_to_class_keyword'                       => true,
        'global_namespace_import'                          => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'increment_style'                                  => ['style' => 'post'],
        'mb_str_functions'                                 => true,
        'modernize_strpos'                                 => true,
        'native_constant_invocation'                       => true,
        'native_function_invocation'                       => ['include' => ['@all']],
        'nullable_type_declaration_for_default_null_value' => true,
        'ordered_imports'                                  => ['imports_order'  => ['class', 'const', 'function'], 'sort_algorithm' => 'alpha'],
        'ordered_interfaces'                               => ['direction' => 'ascend', 'order' => 'alpha'],
        'phpdoc_align'                                     => ['align' => 'left'],
        'phpdoc_line_span'                                 => true,
        'phpdoc_order_by_value'                            => ['annotations' => ['author', 'covers', 'coversNothing', 'dataProvider', 'depends', 'group', 'internal', 'method', 'mixin', 'property', 'property-read', 'property-write', 'requires', 'throws', 'uses']],
        'phpdoc_separation'                                => ['groups' => [['deprecated', 'link', 'see', 'since'], ['author', 'copyright', 'license'], ['category', 'package', 'subpackage'], ['property', 'phpstan-property', 'property-read', 'phpstan-property-read', 'property-write', 'phpstan-property-write'], ['param', 'phpstan-param'], ['return', 'phpstan-return']]],
        'phpdoc_to_comment'                                => false,
        'phpdoc_types_order'                               => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'psr_autoloading'                                  => ['dir' => './src'],
        'regular_callable_call'                            => true,
        'self_static_accessor'                             => true,
        'simplified_if_return'                             => true,
        'single_quote'                                     => ['strings_containing_single_quote_chars' => true],
        'standardize_increment'                            => false,
        'static_lambda'                                    => true,
        'strict_comparison'                                => true,
        'strict_param'                                     => true,
        'trailing_comma_in_multiline'                      => ['elements' => ['arrays', 'arguments', 'match', 'parameters']],
        'yoda_style'                                       => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
    ])
;
