<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Traits;

use Brainshaker95\PhpToTsBundle\Exception\ValidationException;

/**
 * @phpstan-import-type ClientData from MapsToClient
 */
trait ClientDataValidator
{
    /**
     * @param ReflectionProperty[] $properties
     *
     * @phpstan-param ClientData
     *
     * @phpstan-return ClientData
     */
    public function validateClientData(array $properties, array $data): array
    {
        $propertyNames = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            if (!isset($data[$propertyName])) {
                throw new ValidationException(sprintf(
                    'Missing client data property "%s"',
                    $propertyName,
                ));
            }

            $propertyNames[] = $propertyName;
        }

        foreach (array_keys($data) as $propertyName) {
            if (!in_array($propertyName, $propertyNames)) {
                throw new ValidationException(sprintf(
                    'Invalid client data property "%s". Valid properties are: ["%s"]',
                    $propertyName,
                    implode('", "', $propertyNames),
                ));
            }
        }

        return $data;
    }
}
