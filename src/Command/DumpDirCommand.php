<?php

declare(strict_types=1);

namespace Brainshaker95\PhpToTsBundle\Command;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Model\TsEnum;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Tool\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function is_string;

#[AsCommand(
    name: 'phptots:dump:dir',
    description: 'Dumps all TypeScriptables in the given directory',
)]
final class DumpDirCommand extends DumpCommand
{
    protected function configure(): void
    {
        $this->addArgument(
            name: Str::toKebab(C::INPUT_DIR_KEY),
            description: C::INPUT_DIR_DESC,
            mode: InputArgument::OPTIONAL,
        );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config   = $this->getConfig();
        $inputDir = $this->input->getArgument(Str::toKebab(C::INPUT_DIR_KEY));
        $inputDir = is_string($inputDir) ? $inputDir : null;

        $this->io->progressStart();

        $this->dumper->dumpDir($inputDir, $config, function (string $path, TsInterface|TsEnum $tsInterface): void {
            if ($this->isVerbose) {
                $this->fileSuccess($path, $tsInterface);
            }

            $this->io->progressAdvance();
        });

        $this->io->progressFinish();

        return $this->success($config);
    }
}
