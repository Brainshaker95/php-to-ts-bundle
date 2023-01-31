<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Command;

use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'phptots:dump:files',
    description: 'Dumps all TypeScriptables in the given files and directories',
)]
final class DumpFilesCommand extends DumpCommand
{
    protected function configure(): void
    {
        $this->addArgument(
            name: 'input-files',
            description: 'Paths to files to dump',
            mode: InputArgument::REQUIRED | InputArgument::IS_ARRAY,
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var string[]
         */
        $inputFiles = $this->input->getArgument('input-files');

        Assert::nonEmptyStringArrayNonNullable($inputFiles);

        $config = $this->getConfig();

        $this->io->progressStart();

        $this->dumper->dumpFiles($inputFiles, $config, function (string $path, TsInterface $tsInterface): void {
            if ($this->isVerbose) {
                $this->fileSuccess($path, $tsInterface);
            }

            $this->io->progressAdvance();
        });

        $this->io->progressFinish();

        return $this->success($config);
    }
}
