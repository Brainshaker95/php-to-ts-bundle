<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Input;

use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;

/**
 * @internal
 */
#[AsTypeScriptable]
final class SpecialTypes
{
    public const CONSTANT_1 = [
        'foo',
        'bar',
        'baz',
    ];

    public const CONSTANT_2 = [
        'foo1' => 'foo2',
        'bar1' => 'bar2',
        'baz1' => 'baz2',
    ];

    public const CONSTANT_3 = 'foo';
    public const CONSTANT_4 = 'bar';

    /**
     * @var value-of<self::CONSTANT_1>
     */
    public string $testProperty1;

    /**
     * @var key-of<self::CONSTANT_2>
     */
    public string $testProperty2;

    /**
     * @var self::CONSTANT_3|self::CONSTANT_4
     */
    public string $testProperty3;

    public StringEnum $testProperty4;

    public IntEnum $testProperty5;
}
