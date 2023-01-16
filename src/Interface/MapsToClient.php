<?php

namespace Brainshaker95\PhpToTsBundle\Interface;

use ReflectionProperty;

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

    /**
     * @phpstan-param ClientData $data
     *
     * @param ReflectionProperty[] $properties
     *
     * @phpstan-param ClientData $data
     *
     * @phpstan-return ClientData
     */
    public function validateClientData(array $properties, array $data): array;
}
