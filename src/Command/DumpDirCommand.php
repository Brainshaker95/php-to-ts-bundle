<?php

namespace Brainshaker95\PhpToTsBundle\Command;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'phptots:dump:dir',
    description: 'Recursively dumps all TypeScriptables in the given directory',
)]
class DumpDirCommand extends DumpCommand
{
    protected function configure(): void
    {
        $this->addOption(
            name: C::INPUT_DIR_KEY,
            description: C::INPUT_DIR_DESC,
            default: C::INPUT_DIR_DEFAULT,
            mode: InputOption::VALUE_REQUIRED,
            shortcut: 'i',
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dumper->dumpDir(
            self::nonEmptyString($input, C::INPUT_DIR_KEY),
            self::getConfig($input),
        );

        $output->writeln('<info>Types successfully dumped!</info>');

        return self::SUCCESS;
    }
}
