<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Service\Traits;

use Brainshaker95\PhpToTsBundle\Interface\MapsToClient;
use Brainshaker95\PhpToTsBundle\Interface\TypeScriptable;
use Brainshaker95\PhpToTsBundle\Serializer\Serializer;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Service\Attribute\Required;

use function is_string;

trait TsController
{
    private Serializer $serializer;

    #[Required]
    public function setSerializer(Serializer $serializer): void
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array<string,string|string[]|null> $headers
     */
    protected function ts(
        TypeScriptable $typeScriptable,
        int $status = JsonResponse::HTTP_OK,
        array $headers = [],
    ): JsonResponse {
        if ($typeScriptable instanceof MapsToClient) {
            $properties = (new ReflectionClass($typeScriptable))->getProperties(ReflectionProperty::IS_PUBLIC);
            $data       = $typeScriptable->mapToClient($properties);
        } else {
            $data = $this->serializer->serialize($typeScriptable, 'json');
        }

        return new JsonResponse(
            data: $data,
            status: $status,
            headers: $headers,
            json: is_string($data) ? true : false,
        );
    }
}
