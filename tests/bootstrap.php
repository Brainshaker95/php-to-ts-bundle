<?php

declare(strict_types=1);

use Symfony\Component\ErrorHandler\ErrorHandler;

require \dirname(__DIR__) . '/vendor/autoload.php';

// TODO: Follow this issue and remove this hack someday
// See: https://github.com/symfony/symfony/issues/53812#issuecomment-1962740145
\set_exception_handler([new ErrorHandler(), 'handleException']);
