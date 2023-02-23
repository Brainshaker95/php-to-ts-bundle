<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Input\SubDir;

use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;
use Brainshaker95\PhpToTsBundle\Attribute\Hidden;

/**
 * @template T of string class level generic
 *
 * @deprecated because of reasons
 */
#[AsTypeScriptable]
final class GenericTypes
{
    /**
     * @var T|'foo'
     *
     * This is the description for testProperty4
     */
    public string $testProperty4;

    /**
     * @template T of array{
     *     foo: 'bar'|'baz',
     * }
     *
     * @phpstan-ignore-next-line
     *
     * @template U property level generic
     * with a newline
     *
     * @phpstan-ignore-next-line
     *
     * @template V of bool
     *
     * @phpstan-ignore-next-line
     *
     * @template W this is unused
     *
     * This is the description for testProperty5
     *
     * @var array{
     *     foo: ?T,
     *     bar: U,
     *     baz: V|T,
     * }
     */
    public array $testProperty5;

    /**
     * This is the description for testProperty6.
     *
     * @template W of object
     * @template X of object another unused one
     *
     * @phpstan-ignore-next-line
     *
     * @var W
     */
    public object $testProperty6;

    #[Hidden]
    public string $testProperty7;

    /**
     * @phpstan-ignore-next-line
     *
     * @template T of int constructor level generic
     * @template U of array
     *
     * @param T $testProperty1
     * @param T $testProperty2
     * @param U $testProperty3
     */
    public function __construct(
        public int $testProperty1,
        public int $testProperty2,
        public array $testProperty3,
    ) {
    }
}
