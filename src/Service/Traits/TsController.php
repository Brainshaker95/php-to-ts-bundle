<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Service\Traits;

use Brainshaker95\PhpToTsBundle\Interface\TypeScriptable;
use Brainshaker95\PhpToTsBundle\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Service\Attribute\Required;

use function array_merge;

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
     * @param array<string,mixed> $context
     */
    protected function ts(
        TypeScriptable $typeScriptable,
        int $status = JsonResponse::HTTP_OK,
        array $headers = [],
        array $context = [],
    ): JsonResponse {
        $json = $this->serializer->serialize($typeScriptable, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));

        return new JsonResponse($json, $status, $headers, true);
    }
}
