<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Attribute;

use Attribute;

#[Attribute(flags: Attribute::TARGET_CLASS)]
final class AsTypeScriptable {}
