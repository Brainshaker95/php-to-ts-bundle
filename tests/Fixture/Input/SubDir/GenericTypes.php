<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Input\SubDir;

use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;
use Brainshaker95\PhpToTsBundle\Attribute\Hidden;

/**
 * @internal
 *
 * @template T of string class level generic
 *
 * @deprecated because of reasons
 *
 * @PhpCsFixerIgnore global_namespace_import
 */
#[AsTypeScriptable]
final class GenericTypes
{
    /**
     * This is the summary for testProperty4
     * And also this.
     *
     * @var T|'foo'
     */
    public string $testProperty4;

    /**
     * This is the summary for testProperty5.
     *
     * This is the description for testProperty5
     *
     * @template T of array{
     *     foo: 'bar'|'baz',
     * }
     *
     * @phpstan-ignore-next-line
     *
     * @template U property level generic
     * with a newline
     *
     * This should be ignored
     *
     * @phpstan-ignore-next-line
     *
     * @template V of bool
     *
     * @phpstan-ignore-next-line
     *
     * @template W this is unused
     *
     * This should also be ignored
     *
     * @var array{
     *     foo: ?T,
     *     bar: U,
     *     baz: V|T,
     * }
     */
    public array $testProperty5;

    /**
     * This is the summary for testProperty6.
     *
     * This is the description for testProperty6
     *
     * @template W of object
     * @template X of object another unused one
     *
     * @phpstan-ignore-next-line
     *
     * @var W
     */
    public object $testProperty6;

    /**
     * @phpstan-ignore-next-line
     *
     * @var \SomeClass<T>
     */
    public object $testProperty7;

    /**
     * @var int<0, max>
     */
    public int $testProperty8;

    #[Hidden]
    public string $testProperty9;

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
    ) {}
}
