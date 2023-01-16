<?php

namespace Brainshaker95\PhpToTsBundle\Model\Traits;

use Brainshaker95\PhpToTsBundle\Interface\TypeScriptable;
use Brainshaker95\PhpToTsBundle\Model\TsResponse;

trait TsController
{
    /**
     * @param array<string,string|string[]|null> $headers
     */
    protected function ts(
        TypeScriptable $typeScriptable,
        int $status = TsResponse::HTTP_OK,
        array $headers = [],
    ): TsResponse {
        return new TsResponse($typeScriptable, $status, $headers);
    }
}
