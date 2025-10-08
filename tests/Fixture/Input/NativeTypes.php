<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Input;

use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;
use stdClass;

/**
 * @internal
 *
 * @PhpCsFixerIgnore fully_qualified_strict_types
 * @PhpCsFixerIgnore global_namespace_import
 * @PhpCsFixerIgnore nullable_type_declaration
 * @PhpCsFixerIgnore phpdoc_add_missing_param_annotation
 */
#[AsTypeScriptable]
final class NativeTypes
{
    public int $testProperty5;

    public float $testProperty6;

    public string $testProperty7;

    public bool $testProperty8;

    /**
     * @phpstan-ignore-next-line
     */
    public array $testProperty9;

    /**
     * @phpstan-ignore-next-line
     */
    public iterable $testProperty10;

    /**
     * @deprecated
     */
    public mixed $testProperty11;

    /**
     * This is the summary for testProperty12.
     *
     * @deprecated
     */
    public object $testProperty12;

    /**
     * This is the summary for testProperty13.
     *
     * @deprecated it is also deprecated
     */
    public ?int $testProperty13;

    /**
     * This is the summary for testProperty14.
     *
     * This is the description for testProperty14
     * with a newline
     * and another one.
     *
     * And also another paragraph.
     *
     * And even another one
     * with a newline
     */
    public int|null $testProperty14;

    /**
     * @phpstan-ignore-next-line
     */
    public $testProperty15;

    public \stdClass $testProperty16;

    public stdClass $testProperty17;

    /**
     * @phpstan-ignore-next-line
     *
     * @var Foo&Bar
     */
    public object $testProperty18;

    public string|false $testProperty19;

    /**
     * @var true
     */
    public bool $testProperty20;

    public null $testProperty21;

    public true $testProperty22;

    /**
     * @var false
     */
    public $testProperty23;

    /**
     * @phpstan-ignore-next-line
     */
    private $privateProperty2;

    /**
     * @param string $testProperty1 This is the description for testProperty1
     *
     * @deprecated
     *
     * @phpstan-ignore-next-line
     */
    public function __construct(
        public string $testProperty1,
        public readonly string $testProperty2,
        public string $testProperty3,
        public $testProperty4,
        /**
         * @phpstan-ignore-next-line
         */
        private string $privateProperty1,
    ) {}
}
