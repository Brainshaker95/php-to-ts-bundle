<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Service\Traits;

use Brainshaker95\PhpToTsBundle\Serializer\Serializer;
use Symfony\Contracts\Service\Attribute\Required;

trait HasSerializer
{
    protected Serializer $serializer;

    #[Required]
    final public function setSerializer(Serializer $serializer): void
    {
        $this->serializer = $serializer;
    }
}
