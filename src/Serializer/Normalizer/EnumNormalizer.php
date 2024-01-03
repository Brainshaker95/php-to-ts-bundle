<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Serializer\Normalizer;

use ArrayObject;
use BackedEnum;
use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;
use Brainshaker95\PhpToTsBundle\Tool\Attribute;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

use function get_debug_type;
use function is_object;
use function sprintf;

final class EnumNormalizer implements NormalizerInterface
{
    /**
     * @param mixed[] $context
     *
     * @return array<mixed>|string|int|float|bool|ArrayObject<int|string,mixed>|null
     */
    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): array|string|int|float|bool|ArrayObject|null {
        if (!is_object($data)) {
            throw new InvalidArgumentException(sprintf(
                'Expected paramteter 1 ($data) to be of type "object" but got "%s".',
                get_debug_type($data),
            ));
        }

        if (!$data instanceof BackedEnum) {
            throw new InvalidArgumentException(sprintf(
                'Expected object to be an instance of "%s". Given instance was of class "%s".',
                BackedEnum::class,
                $data::class,
            ));
        }

        return $data->value;
    }

    /**
     * @param mixed[] $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof BackedEnum
            && Attribute::existsOnClass(AsTypeScriptable::class, $data);
    }

    /**
     * @return array<class-string|'*'|'object'|string,bool|null>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            BackedEnum::class => true,
        ];
    }
}
