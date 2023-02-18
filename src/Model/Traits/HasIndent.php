<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Traits;

use Brainshaker95\PhpToTsBundle\Model\Config\Indent;

/**
 * @internal
 */
trait HasIndent
{
    private ?Indent $indent = null;

    public function setIndent(Indent $indent): static
    {
        $this->indent = $indent;

        return $this;
    }
}
