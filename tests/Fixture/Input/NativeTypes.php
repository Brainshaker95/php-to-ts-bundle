<?php

declare(strict_types=1);

namespace App\Tests\Fixture\Input;

use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;
use Brainshaker95\PhpToTsBundle\Attribute\Hidden;

/**
 * @internal
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
     * This is the description for testProperty12.
     *
     * @deprecated
     */
    public object $testProperty12;

    /**
     * This is the description for testProperty13.
     *
     * @deprecated it is also deprecated
     */
    public ?int $testProperty13;

    /**
     * This is the description for testProperty14
     * with a newline
     * and another one.
     */
    public int|null $testProperty14;

    /**
     * @phpstan-ignore-next-line
     */
    public $testProperty15;

    /**
     * @phpstan-ignore-next-line
     */
    private $testProperty16;

    /**
     * This is the constructor description.
     *
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
        #[Hidden] public $hiddenProperty,
        /**
         * @phpstan-ignore-next-line
         */
        private string $privateProperty1,
    ) {
    }
}
