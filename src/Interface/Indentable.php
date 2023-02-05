<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Interface;

use Brainshaker95\PhpToTsBundle\Model\Config\Indent;

/**
 * @internal
 */
interface Indentable
{
    public function setIndent(Indent $indent): self;
}
