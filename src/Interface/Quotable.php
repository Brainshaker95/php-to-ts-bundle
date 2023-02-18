<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Interface;

use Brainshaker95\PhpToTsBundle\Model\Config\Quotes;

/**
 * @internal
 */
interface Quotable
{
    public function setQuotes(Quotes $quotes): static;
}
