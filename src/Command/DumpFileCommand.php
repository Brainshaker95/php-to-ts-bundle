<?php

namespace Brainshaker95\PhpToTsBundle\Command;

use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'phptots:dump:file',
    description: 'Dumps all TypeScriptables in the given file',
)]
class DumpFileCommand extends DumpCommand
{
    protected function configure(): void
    {
        $this->addOption(
            name: 'input-file',
            description: 'Path to file to dump',
            mode: InputOption::VALUE_REQUIRED,
            shortcut: 'i',
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->input->getOption('input-file')) {
            throw new InvalidOptionException('The "--input-file" option to be defined.');
        }

        $inputFile = Assert::nonEmptyString($this->input->getOption('input-file'));
        $config    = $this->getConfig();

        $this->io->progressStart();

        $this->dumper->dumpFile($inputFile, $config, function (string $path, TsInterface $tsInterface) {
            if ($this->isVerbose) {
                $this->fileSuccess($path, $tsInterface);
            }

            $this->io->progressAdvance();
        });

        $this->io->progressFinish();

        return $this->success($config);
    }
}
