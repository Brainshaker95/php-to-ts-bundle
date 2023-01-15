<?php

namespace Brainshaker95\PhpToTsBundle\Command;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Model\Config\PartialConfig;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Service\Dumper;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;

abstract class DumpCommand extends Command
{
    private const INDENT_STYLE_KEY = C::INDENT_KEY . '-' . C::INDENT_STYLE_KEY;
    private const INDENT_COUNT_KEY = C::INDENT_KEY . '-' . C::INDENT_COUNT_KEY;

    #[Required]
    public Dumper $dumper;

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

    protected function getConfig(): C
    {
        $outputDir        = $this->input->getOption(Str::toKebab(C::OUTPUT_DIR_KEY));
        $fileType         = $this->input->getOption(Str::toKebab(C::FILE_TYPE_KEY));
        $indentStyle      = $this->input->getOption(self::INDENT_STYLE_KEY);
        $indentCount      = $this->input->getOption(self::INDENT_COUNT_KEY);
        $sortStrategies   = $this->input->getOption(Str::toKebab(C::SORT_STRATEGIES_KEY));
        $fileNameStrategy = $this->input->getOption(Str::toKebab(C::FILE_NAME_STRATEGY_KEY));

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
