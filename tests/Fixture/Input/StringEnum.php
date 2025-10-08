<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Input;

use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;

/**
 * @internal
 */
#[AsTypeScriptable]
enum StringEnum: string
{
    case CASE_1 = 'Case 1';
    case CASE_2 = 'Case 2';
    case CASE_3 = 'Case 3';
    case CASE_4 = 'Case 4';
    case CASE_5 = 'Case 5';
}
