<?php

namespace Brainshaker95\PhpToTsBundle\Command;

use Brainshaker95\PhpToTsBundle\Service\Dumper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'phptots:dump',
    description: 'Convert PHP model classes to TypeScript interfaces',
)]
class DumpCommand extends Command
{
    #[Required]
    public Dumper $dumper;

    protected function configure(): void
    {
        $this
            ->addArgument(
                'output_path',
                InputArgument::OPTIONAL,
                'The path where the TypeScript interfaces will be dumped',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // $outputPath = $input->getArgument('output_path');

        $this->dumper->dumpDir();

        $output->writeln('<info>Types successfully dumped!</info>');

        return Command::SUCCESS;
    }
}
