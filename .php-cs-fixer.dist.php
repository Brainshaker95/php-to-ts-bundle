<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__ . '/src');

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                 => true,
        '@PhpCsFixer'            => true,
        '@PHP81Migration'        => true,
        'binary_operator_spaces' => [
            'operators' => [
                '='   => 'align_single_space_minimal',
                '=>'  => 'align_single_space_minimal',
                '??=' => 'align_single_space_minimal',
                '.='  => 'align_single_space_minimal',
                '+='  => 'align_single_space_minimal',
                '-='  => 'align_single_space_minimal',
                '*='  => 'align_single_space_minimal',
                '/='  => 'align_single_space_minimal',
                '%='  => 'align_single_space_minimal',
                '**=' => 'align_single_space_minimal',
                '&='  => 'align_single_space_minimal',
                '|='  => 'align_single_space_minimal',
                '^='  => 'align_single_space_minimal',
                '<<=' => 'align_single_space_minimal',
                '>>=' => 'align_single_space_minimal',
            ],
        ],
        'class_definition'            => ['multi_line_extends_each_single_line' => true],
        'concat_space'                => ['spacing' => 'one'],
        'global_namespace_import'     => ['import_classes' => true, 'import_constants' => null, 'import_functions' => null],
        'modernize_strpos'            => true,
        'phpdoc_align'                => ['align' => 'left'],
        'phpdoc_no_empty_return'      => false,
        'phpdoc_to_comment'           => false,
        'phpdoc_types_order'          => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'standardize_increment'       => false,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'yoda_style'                  => false,
    ])
;
