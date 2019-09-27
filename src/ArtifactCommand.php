<?php

namespace Selective\Artifact;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Artifact Command.
 */
class ArtifactCommand extends Command
{
    /**
     * Configure.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('artifact')->setDescription('An artifact generator for deployments.');
        $this->addOption('build', null, InputOption::VALUE_REQUIRED, 'Build the artifact.');

    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int A 0 on success, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->printApplicationTitle($output);

        $filesystem = new ArtifactFilesystem();
        $generator = new ArtifactGenerator($input, $output, $filesystem);

        if ($input->hasOption('build')) {
            $generator->buildArtifact();
        }

        return 0;
    }

    /**
     * Print application name.
     *
     * @param OutputInterface $output
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
