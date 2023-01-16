<?php

namespace Brainshaker95\PhpToTsBundle\Interface;

/**
 * @phpstan-type ClientData array<string,string|int|float|bool|mixed[]|null>
 */
interface MapsToClient
{
    /**
     * @param ReflectionProperty[] $properties
     *
     * @phpstan-return ClientData
     */
    public function mapToClient(array $properties): array;
}
