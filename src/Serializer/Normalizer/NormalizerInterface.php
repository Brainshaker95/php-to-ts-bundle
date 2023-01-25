<?php

namespace Brainshaker95\PhpToTsBundle\Serializer\Normalizer;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as SymfonyNormalizerInterface;

#[AutoconfigureTag('php_to_ts.serializer.normalizer')]
interface NormalizerInterface extends SymfonyNormalizerInterface
{
}