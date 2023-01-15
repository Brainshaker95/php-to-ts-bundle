<?php

namespace Brainshaker95\PhpToTsBundle\Command;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Model\Config\PartialConfig;
use Brainshaker95\PhpToTsBundle\Service\Dumper;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Contracts\Service\Attribute\Required;

abstract class DumpCommand extends Command
{
    private const INDENT_STYLE_KEY = C::INDENT_KEY . '-' . C::INDENT_STYLE_KEY;
    private const INDENT_COUNT_KEY = C::INDENT_KEY . '-' . C::INDENT_COUNT_KEY;

    #[Required]
    public Dumper $dumper;

    protected function configure(): void
    {
        $this
            ->addOption(
                name: Str::toKebab(C::OUTPUT_DIR_KEY),
                description: C::OUTPUT_DIR_DESC,
                default: C::OUTPUT_DIR_DEFAULT,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'o',
            )
            ->addOption(
                name: Str::toKebab(C::FILE_TYPE_KEY),
                description: C::FILE_TYPE_DESC,
                default: C::FILE_TYPE_DEFAULT,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 't',
            )
            ->addOption(
                name: Str::toKebab(self::INDENT_STYLE_KEY),
                description: C::INDENT_STYLE_DESC,
                default: C::INDENT_STYLE_DEFAULT,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'p',
            )
            ->addOption(
                name: Str::toKebab(self::INDENT_COUNT_KEY),
                description: C::INDENT_COUNT_DESC,
                default: C::INDENT_COUNT_DEFAULT,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'c',
            )
            ->addOption(
                name: Str::toKebab(C::SORT_STRATEGIES_KEY),
                description: C::SORT_STRATEGIES_DESC,
                default: C::SORT_STRATEGIES_DEFAULT,
                mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                shortcut: 's',
            )
            ->addOption(
                name: Str::toKebab(C::FILE_NAME_STRATEGY_KEY),
                description: C::FILE_NAME_STRATEGY_DESC,
                default: C::FILE_NAME_STRATEGY_DEFAULT,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'f',
            )
        ;
    }

    protected static function getConfig(InputInterface $input): C
    {
        $outputDir        = $input->getOption(Str::toKebab(C::OUTPUT_DIR_KEY));
        $fileType         = $input->getOption(Str::toKebab(C::FILE_TYPE_KEY));
        $indentStyle      = $input->getOption(self::INDENT_STYLE_KEY);
        $indentCount      = $input->getOption(self::INDENT_COUNT_KEY);
        $sortStrategies   = $input->getOption(Str::toKebab(C::SORT_STRATEGIES_KEY));
        $fileNameStrategy = $input->getOption(Str::toKebab(C::FILE_NAME_STRATEGY_KEY));

        return PartialConfig::fromArray([
            C::OUTPUT_DIR_KEY         => Assert::nonEmptyString($outputDir),
            C::FILE_TYPE_KEY          => Assert::nonEmptyString($fileType),
            C::INDENT_KEY             => [
                C::INDENT_STYLE_KEY => Assert::nonEmptyString($indentStyle),
                C::INDENT_COUNT_KEY => Assert::nonNegativeInteger($indentCount),
            ],
            C::SORT_STRATEGIES_KEY    => Assert::nonEmptyStringArray($sortStrategies),
            C::FILE_NAME_STRATEGY_KEY => Assert::nonEmptyString($fileNameStrategy),
        ]);
    }
}
