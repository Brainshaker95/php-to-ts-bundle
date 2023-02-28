<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Command;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Model\Config\PartialConfig;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Service\Traits\HasDumper;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function count;
use function sprintf;

abstract class DumpCommand extends Command
{
    use HasDumper;

    final public const INDENT_STYLE_KEY = C::INDENT_KEY . '-' . C::INDENT_STYLE_KEY;
    final public const INDENT_COUNT_KEY = C::INDENT_KEY . '-' . C::INDENT_COUNT_KEY;

    protected InputInterface $input;

    protected OutputInterface $output;

    protected SymfonyStyle $io;

    protected bool $isVerbose;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->input     = $input;
        $this->output    = $output;
        $this->io        = new SymfonyStyle($input, $output);
        $this->isVerbose = $output->isVerbose();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                name: Str::toKebab(C::OUTPUT_DIR_KEY),
                description: C::OUTPUT_DIR_DESC,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'o',
            )
            ->addOption(
                name: Str::toKebab(C::FILE_TYPE_KEY),
                description: C::FILE_TYPE_DESC,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'f',
            )
            ->addOption(
                name: Str::toKebab(C::TYPE_DEFINITION_TYPE_KEY),
                description: C::TYPE_DEFINITION_TYPE_DESC,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 't',
            )
            ->addOption(
                name: Str::toKebab(self::INDENT_STYLE_KEY),
                description: C::INDENT_STYLE_DESC,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'p',
            )
            ->addOption(
                name: Str::toKebab(self::INDENT_COUNT_KEY),
                description: C::INDENT_COUNT_DESC,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'c',
            )
            ->addOption(
                name: Str::toKebab(C::QUOTES_KEY),
                description: C::QUOTES_DESC,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'u',
            )
            ->addOption(
                name: Str::toKebab(C::SORT_STRATEGIES_KEY),
                description: C::SORT_STRATEGIES_DESC,
                mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                shortcut: 's',
            )
            ->addOption(
                name: Str::toKebab(C::FILE_NAME_STRATEGY_KEY),
                description: C::FILE_NAME_STRATEGY_DESC,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'w',
            )
        ;
    }

    protected function getConfig(): C
    {
        $outputDir          = $this->input->getOption(Str::toKebab(C::OUTPUT_DIR_KEY));
        $fileType           = $this->input->getOption(Str::toKebab(C::FILE_TYPE_KEY));
        $typeDefinitionType = $this->input->getOption(Str::toKebab(C::TYPE_DEFINITION_TYPE_KEY));
        $indentStyle        = $this->input->getOption(self::INDENT_STYLE_KEY);
        $indentCount        = $this->input->getOption(self::INDENT_COUNT_KEY);
        $quotes             = $this->input->getOption(Str::toKebab(C::QUOTES_KEY));
        $sortStrategies     = $this->input->getOption(Str::toKebab(C::SORT_STRATEGIES_KEY));
        $fileNameStrategy   = $this->input->getOption(Str::toKebab(C::FILE_NAME_STRATEGY_KEY));

        $indentStyle    = Assert::nonEmptyStringNullable($indentStyle);
        $indentCount    = Assert::nonNegativeIntegerNullable($indentCount);
        $sortStrategies = Assert::nonEmptyStringArrayNullable($sortStrategies) ?? [];

        return PartialConfig::fromArray([
            C::OUTPUT_DIR_KEY           => Assert::nonEmptyStringNullable($outputDir),
            C::FILE_TYPE_KEY            => Assert::nonEmptyStringNullable($fileType),
            C::TYPE_DEFINITION_TYPE_KEY => Assert::nonEmptyStringNullable($typeDefinitionType),
            C::INDENT_KEY               => ($indentStyle !== null || $indentCount !== null) ? [
                C::INDENT_STYLE_KEY => $indentStyle,
                C::INDENT_COUNT_KEY => $indentCount,
            ] : null,
            C::QUOTES_KEY             => Assert::nonEmptyStringNullable($quotes),
            C::SORT_STRATEGIES_KEY    => count($sortStrategies) > 0 ? $sortStrategies : null,
            C::FILE_NAME_STRATEGY_KEY => Assert::nonEmptyStringNullable($fileNameStrategy),
        ]);
    }

    protected function fileSuccess(string $path, TsInterface $tsInterface): void
    {
        $this->io->text(sprintf(
            '<fg=#0b0>%s</> -> <fg=#bb0>%s</>',
            $tsInterface->name,
            $path,
        ));
    }

    protected function success(C $config): int
    {
        $this->io->block(
            messages: sprintf(
                'Successfully dumped types to "%s"',
                $config->getOutputDir() ?? C::OUTPUT_DIR_DEFAULT,
            ),
            type: 'OK',
            style: 'fg=#0b0',
            padding: true,
        );

        return self::SUCCESS;
    }
}
