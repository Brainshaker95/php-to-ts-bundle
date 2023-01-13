<?php

namespace Brainshaker95\PhpToTsBundle\Interface;

interface FileNameStrategy
{
    public function getName(string $name): string;
}
