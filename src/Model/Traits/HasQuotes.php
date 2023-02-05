<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Traits;

use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;

/**
 * @internal
 */
trait HasQuotes
{
    private ?Quotes $quotes = null;

    public function setQuotes(Quotes $quotes): self
    {
        $this->quotes = $quotes;

        return $this;
    }
}
