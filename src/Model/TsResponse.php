<?php

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Interface\MapsToClient;
use Brainshaker95\PhpToTsBundle\Interface\TypeScriptable;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TsResponse extends JsonResponse
{
    private static JsonEncoder $encoder;

    private static ObjectNormalizer $normalizer;

    private static Serializer $serializer;

    /**
     * @param array<string,string|string[]|null> $headers
     */
    public function __construct(
        TypeScriptable $typeScriptable,
        int $status = self::HTTP_OK,
        array $headers = [],
    ) {
        self::$normalizer ??= new ObjectNormalizer();
        self::$encoder    ??= new JsonEncoder();
        self::$serializer ??= new Serializer([self::$normalizer], [self::$encoder]);

        if ($typeScriptable instanceof MapsToClient) {
            $properties = (new ReflectionClass($typeScriptable))->getProperties(ReflectionProperty::IS_PUBLIC);
            $data       = $typeScriptable->mapToClient($properties);
        } else {
            $data = self::$serializer->normalize($typeScriptable);
        }

        parent::__construct($data, $status, $headers);
    }
}
