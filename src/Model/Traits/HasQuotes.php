<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Traits;

use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;
use Override;

/**
 * @internal
 */
trait HasQuotes
{
    private ?Quotes $quotes = null;

    #[Override]
    public function setQuotes(Quotes $quotes): static
    {
        $this->quotes = $quotes;

        return $this;
    }
}
