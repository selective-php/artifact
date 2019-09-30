<?php

namespace Selective\Artifact\Command;

use Selective\Artifact\Builder\ArtifactBuilder;
use Selective\Artifact\Builder\ArtifactFilesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Build Command.
 */
class BuildCommand extends Command
{
    /**
     * Configure.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Artifact Generator');

        $this->setName('build')->setDescription('Build the artifact');
        $this->addOption('test', null, InputOption::VALUE_NONE);
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input The console input
     * @param OutputInterface $output The console output
     *
     * @return int The error level, 0 on success, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->printApplicationTitle($output);

        $filesystem = new ArtifactFilesystem();
        $generator = new ArtifactBuilder($output, $filesystem);

        if ($input->getOption('test')) {
            $output->writeln('Test mode');
        } else {
            $generator->buildArtifact();
        }

        return 0;
    }

    /**
     * Print application name.
     *
     * @param OutputInterface $output The console output
     *
     * @return void
     */
    protected function printApplicationTitle(OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln('Artifact Generator');
        $output->writeln('');
    }
}