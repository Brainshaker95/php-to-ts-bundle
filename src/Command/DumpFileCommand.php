<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Command;

use Brainshaker95\PhpToTsBundle\Model\TsEnum;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'phptots:dump:file',
    description: 'Dumps all TypeScriptables in the given file',
)]
final class DumpFileCommand extends DumpCommand
{
    protected function configure(): void
    {
        $this->addArgument(
            name: 'input-file',
            description: 'Path to file to dump',
            mode: InputArgument::REQUIRED,
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var string
         */
        $inputFile = $this->input->getArgument('input-file');

        Assert::nonEmptyStringNonNullable($inputFile);

        $config = $this->getConfig();

        $this->io->progressStart();

        $this->dumper->dumpFile($inputFile, $config, function (string $path, TsInterface|TsEnum $tsInterface): void {
            if ($this->isVerbose) {
                $this->fileSuccess($path, $tsInterface);
            }

            $this->io->progressAdvance();
        });

        $this->io->progressFinish();

        return $this->success($config);
    }
}
