<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Serializer\Encoder;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Encoder\EncoderInterface as SymfonyEncoderInterface;

#[AutoconfigureTag(name: 'php_to_ts.serializer.encoder')]
interface EncoderInterface extends SymfonyEncoderInterface {}
