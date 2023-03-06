<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Service\Traits;

use Brainshaker95\PhpToTsBundle\Service\Dumper;
use Symfony\Contracts\Service\Attribute\Required;

trait HasDumper
{
    protected Dumper $dumper;

    #[Required]
    final public function setDumper(Dumper $dumper): void
    {
        $this->dumper = $dumper;
    }
}
