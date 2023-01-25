<?php

namespace Brainshaker95\PhpToTsBundle\Model;

use Brainshaker95\PhpToTsBundle\Model\Config\Indent;

use Stringable;

class TsProperty implements Stringable
{
    public const TYPE_ANY     = 'any';
    public const TYPE_ARRAY   = 'array';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_NULL    = 'null';
    public const TYPE_OBJECT  = 'object';
    public const TYPE_NUMBER  = 'number';
    public const TYPE_STRING  = 'string';
    public const TYPE_UNKNOWN = 'unknown';

    public function __construct(
        public string $name,
        public string $type,
        public readonly bool $isReadonly = false,
        public readonly bool $isConstructorProperty = false,
    ) {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(Indent $indent = new Indent()): string
    {
        return sprintf(
            '%s%s%s: %s;',
            $indent->toString(),
            $this->isReadonly ? 'readonly ' : '',
            $this->name,
            $this->type,
        );
    }
}
