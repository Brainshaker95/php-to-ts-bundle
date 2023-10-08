<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Input;

use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;

/**
 * @internal
 *
 * This is a class description
 */
#[AsTypeScriptable]
final class IterableTypes
{
    /**
     * @var string[]
     */
    public array $testProperty1;

    /**
     * @var string[][]
     */
    public iterable $testProperty2;

    /**
     * @var array<int>
     */
    public array $testProperty3;

    /**
     * @var array<string,boolean>
     */
    public array $testProperty4;

    /**
     * @var iterable<string,array<int>>
     */
    public iterable $testProperty5;

    /**
     * @var array<string,array<int,string[]>>
     */
    public array $testProperty6;

    /**
     * @var array{
     *     foo: string,
     *     bar: array{
     *         foo: list{
     *             foo: int,
     *         },
     *     },
     *     baz: ?array{
     *         foo: boolean|'foo',
     *     },
     * }
     */
    public array $testProperty7;

    /**
     * @var list{}
     */
    public array $testProperty8;

    /**
     * @var array{
     *     string,
     *     ...,
     * }
     */
    public array $testProperty9;

    /**
     * @var array{
     *     0: 1.0|string,
     *     'foo': string,
     *     ...,
     * }
     */
    public array $testProperty10;
}
