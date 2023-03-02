<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 *
 * @covers \Brainshaker95\PhpToTsBundle\Serializer\Serializer
 */
final class SeializerTest extends KernelTestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $container  = self::getContainer();
        $serializer = $container->get(Serializer::class);

        self::assertInstanceOf(Serializer::class, $serializer);

        $this->serializer = $serializer;
    }

    public function testSerializer(): void
    {
        /**
         * @phpstan-ignore-next-line - This is just a throwaway anonymous class used for this test
         */
        $instance = new class(true, ['foo' => ['bar' => ['baz']]]) {
            public int $property1;

            public string $property2;

            /**
             * @param array<string,array<string,string[]>> $property4
             */
            public function __construct(
                public bool $property3,
                public array $property4,
            ) {
            }
        };

        $instance->property1 = 1;
        $instance->property2 = '1';

        self::assertSame(
            expected: '{"property1":1,"property2":"1","property3":true,"property4":{"foo":{"bar":["baz"]}}}',
            actual: $this->serializer->serialize($instance, 'json'),
        );
    }
}
