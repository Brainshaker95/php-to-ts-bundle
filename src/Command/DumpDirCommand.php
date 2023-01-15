<?php

namespace Brainshaker95\PhpToTsBundle\Command;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'phptots:dump:dir',
    description: 'Dumps all TypeScriptables in the given directory',
)]
class DumpDirCommand extends DumpCommand
{
    protected function configure(): void
    {
        $this->addOption(
            name: Str::toKebab(C::INPUT_DIR_KEY),
            description: C::INPUT_DIR_DESC,
            default: C::INPUT_DIR_DEFAULT,
            mode: InputOption::VALUE_REQUIRED,
            shortcut: 'i',
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputDir = Assert::nonEmptyString($this->input->getOption(Str::toKebab(C::INPUT_DIR_KEY)));
        $config   = $this->getConfig();

        $this->io->progressStart();

        $this->dumper->dumpDir($inputDir, $config, function (string $path, TsInterface $tsInterface) {
            if ($this->isVerbose) {
                $this->fileSuccess($path, $tsInterface);
            }

            $this->io->progressAdvance();
        });

        $this->io->progressFinish();

        return $this->success($config);
    }
}
