<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Traits;

use Brainshaker95\PhpToTsBundle\Model\TsDocComment;
use Symfony\Component\String\UnicodeString;

use const PHP_EOL;

use function Symfony\Component\String\u;

/**
 * @internal
 */
trait HasTsInterfaceHeader
{
    private static function getHeader(): UnicodeString
    {
        $header = (new TsDocComment(
            description: "Auto-generated by PhpToTsBundle\nDo not modify directly!",
        ))->toString();

        return u($header)
            ->append(PHP_EOL)
            ->append(PHP_EOL)
        ;
    }
}
