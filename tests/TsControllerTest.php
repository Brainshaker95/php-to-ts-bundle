<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;
use Brainshaker95\PhpToTsBundle\Serializer\Serializer;
use Brainshaker95\PhpToTsBundle\Service\Traits\TsController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 *
 * @small
 *
 * @covers \Brainshaker95\PhpToTsBundle\Serializer\Serializer
 * @covers \Brainshaker95\PhpToTsBundle\Service\Traits\HasSerializer
 * @covers \Brainshaker95\PhpToTsBundle\Service\Traits\TsController
 */
final class TsControllerTest extends KernelTestCase
{
    use TsController;

    protected function setUp(): void
    {
        $container  = self::getContainer();
        $serializer = $container->get(Serializer::class);

        self::assertInstanceOf(Serializer::class, $serializer);

        $this->setSerializer($serializer);
    }

    public function testTsController(): void
    {
        $instance = new #[AsTypeScriptable] class(true, ['foo' => ['bar' => ['baz']]]) {
            public int $property1;

            public string $property2;

            /**
             * @param array<string,array<string,string[]>> $property4
             */
            public function __construct(
                public bool $property3,
                public array $property4,
            ) {}
        };

        $instance->property1 = 1;
        $instance->property2 = '1';

        $response = $this->ts($instance);

        self::assertTrue($response->getStatusCode() === Response::HTTP_OK);
        self::assertInstanceOf(JsonResponse::class, $response);

        $expected = '{"property1":1,"property2":"1","property3":true,"property4":{"foo":{"bar":["baz"]}}}';

        self::assertSame(
            expected: $expected,
            actual: $this->serializer->serialize($instance, 'json'),
        );

        self::assertSame(
            expected: $expected,
            actual: $response->getContent(),
        );
    }
}
