<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Input;

use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;

/**
 * @internal
 */
#[AsTypeScriptable]
enum IntEnum: int
{
    case CASE_1 = 0;
    case CASE_2 = 1;
    case CASE_3 = 2;
    case CASE_4 = 4;
    case CASE_5 = 8;
}
