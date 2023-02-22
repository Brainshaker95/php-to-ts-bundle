<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Service\Traits;

use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;
use Brainshaker95\PhpToTsBundle\Exception\AssertionFailedException;
use Brainshaker95\PhpToTsBundle\Serializer\Serializer;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
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
     * Returns a JsonResponse that uses the PhpToTs serializer.
     *
     * @param mixed $typeScriptable The TypeScriptable to serialize
     * @param array<string,string|string[]|null> $headers The HTTP headers of the response
     * @param array<string,mixed> $context Options normalizers/encoders have access to
     *
     * @throws AssertionFailedException When the given TypeScriptable is not tagged with the `AsTypeScriptable` attribute
     */
    protected function ts(
        mixed $typeScriptable,
        int $status = JsonResponse::HTTP_OK,
        array $headers = [],
        array $context = [],
    ): JsonResponse {
        Assert::existingClassAttribute($typeScriptable, AsTypeScriptable::class);

        $json = $this->serializer->serialize($typeScriptable, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));

        return new JsonResponse($json, $status, $headers, true);
    }
}
