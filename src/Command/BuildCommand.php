<?php

namespace Selective\Artifact\Command;

use Selective\Artifact\Builder\ArtifactBuilder;
use Selective\Artifact\Compression\ZipFile;
use Selective\Artifact\Filesystem\LocalFilesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Build Command.
 */
final class BuildCommand extends Command
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
        $this->addOption('name', null, InputOption::VALUE_OPTIONAL, 'The zip file prefix', 'my_app');
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

        $filesystem = new LocalFilesystem();
        $zipFile = new ZipFile();
        $artifactBuilder = new ArtifactBuilder($output, $filesystem, $zipFile);
        $name = $input->getOption('name');
        $name = is_string($name) ? $name : '';

        if ($input->getOption('test')) {
            $output->writeln('Test mode');
        } else {
            $artifactBuilder->buildArtifact($name);
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
