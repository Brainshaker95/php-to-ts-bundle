<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Interface;

use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;

/**
 * @internal
 */
interface QuotesAware
{
    public function setQuotes(?Quotes $quotes): self;
}
