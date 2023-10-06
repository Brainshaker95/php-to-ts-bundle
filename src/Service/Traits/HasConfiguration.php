<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Service\Traits;

use Brainshaker95\PhpToTsBundle\Service\Configuration;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @internal
 */
trait HasConfiguration
{
    protected Configuration $config;

    #[Required]
    final public function setConfiguration(Configuration $config): void
    {
        $this->config = $config;
    }
}
