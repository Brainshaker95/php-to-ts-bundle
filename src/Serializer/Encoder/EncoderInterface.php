<?php

namespace Brainshaker95\PhpToTsBundle\Serializer\Encoder;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Encoder\EncoderInterface as SymfonyEncoderInterface;

#[AutoconfigureTag('php_to_ts.serializer.encoder')]
interface EncoderInterface extends SymfonyEncoderInterface
{
}