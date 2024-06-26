<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Model\Traits;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Model\Config\FileType;

/**
 * @internal
 */
trait HasFileName
{
    /**
     * Gets the file based on the configured file name strategy and file type.
     */
    public function getFileName(): string
    {
        $fileNameStrategy = $this->config?->getFileNameStrategy() ?? C::FILE_NAME_STRATEGY_DEFAULT;
        $fileType         = $this->config?->getFileType() ?? C::FILE_TYPE_DEFAULT;

        return (new $fileNameStrategy())->getName($this->name)
            . ($fileType === FileType::TYPE_DECLARATION ? '.d' : '')
            . '.ts';
    }
}
