<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Attribute;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final class Hidden {}
