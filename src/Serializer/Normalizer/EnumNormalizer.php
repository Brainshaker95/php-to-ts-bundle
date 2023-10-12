<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Serializer\Normalizer;

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
     */
    public function normalize(
        mixed $enum,
        ?string $format = null,
        array $context = [],
    ): int|string {
        if (!is_object($enum)) {
            throw new InvalidArgumentException(sprintf(
                'Expected paramteter 1 ($enum) to be of type "object" but got "%s".',
                get_debug_type($enum),
            ));
        }

        if (!$enum instanceof BackedEnum) {
            throw new InvalidArgumentException(sprintf(
                'Expected object to be an instance of "%s". Given instance was of class "%s".',
                BackedEnum::class,
                $enum::class,
            ));
        }

        return $enum->value;
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof BackedEnum
            && Attribute::existsOnClass(AsTypeScriptable::class, $data);
    }
}
