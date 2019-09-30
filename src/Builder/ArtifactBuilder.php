<?php

namespace Selective\Artifact\Builder;

use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Artifact Generator.
 */
final class ArtifactBuilder
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ArtifactFilesystem The filesystem
     */
    private $filesystem;

    /**
     * @var array File set to delete
     */
    private $fileset = [
        // Files
        '^composer\.json$',
        '^composer\.lock$',
        '\/?phpunit\.xml$',
        '\/?phpunit\.xml\.dist$',
        '\/?phpcs\.xml$',
        '\/?phpstan\.neon$',
        '\/?phpstan\.neon\.dist$',
        '\/?\.gitignore$',
        '\/?\.codeclimate.yml$',
        '\/?\.editorconfig',
        '\/?\.styleci.yml$',
        '\/?\.scrutinizer.yml$',
        '\/?\.travis.yml$',
        '\/?\.appveyor.yml$',
        '\/?\.coveralls.yml$',
        '\/?\.mkdocs.yml$',
        '\/?\.phpstan.neon$',
        '\/?\.appveyor.yml$',
        '\/?\.build.xml$',
        '\/?\.eslintrc.json$',
        '\/?\.gitignore$',
        '\/?\.gitattributes$',
        '\/?\.cs\.php$',
        '\/?\.phpstorm\.meta\.php$',
        '\/?CHANGELOG\.md$',
        '\/?CONDUCT\.md$',
        '\/?LICENSE$',
        '\/?LICENSE\.md$',
        '\/?LICENSE\.txt$',
        '\/?README$',
        '\/?README\.md$',
        '\/?README\.rst$',
        '\/?CHANGES$',
        '\/?CHANGES\.md$',
        '\/?CHANGELOG$',
        '\/?CHANGELOG$\.md$',
        '\/?CONTRIBUTING$',
        '\/?CONTRIBUTING\.md$',
        '\/?MAINTAINERS$',
        '\/?MAINTAINERS\.md$',
        '\/?UPGRADE$',
        '\/?UPGRADE\.md$',
        '\/?UPGRADE_(.*)\.md$',
        '\/?jest\.config\.js$',
        '\/?webpack\.config\.js$',
        '\/?package\.json$',
        '\/?package\-lock\.json$',
        '\/?tsconfig\.json$',
        // Direcotries
        '\/?test$',
        '\/?tester$',
        '\/?docs$',
        '\/?doc$',
        '\/?examples$',
        '\/?example$',
    ];

    /**
     * The constructor.
     *
     * @param OutputInterface $output The output
     * @param ArtifactFilesystem $filesystem The filesystem
     */
    public function __construct(
        OutputInterface $output,
        ArtifactFilesystem $filesystem
    ) {
        $this->output = $output;
        $this->filesystem = $filesystem;
    }

    /**
     * Generate artifact file.
     *
     * @throws RuntimeException
     *
     * @return void
     */
    public function buildArtifact()
    {
        $currentDir = $this->filesystem->normalizePath((string)getcwd());
        $baseDir = $this->filesystem->normalizePath($currentDir);
        $buildDir = $this->filesystem->normalizePath(sprintf('%s/build', $baseDir));
        $masterDir = $this->filesystem->normalizePath(sprintf('%s/master', $buildDir));
        $zipFile = $this->filesystem->normalizePath(sprintf('%s/my_app_%s.zip', $buildDir, date('YmdHis')));
        $masterZip = "$buildDir/master.zip";

        $this->filesystem->createDirectory($buildDir);
        $this->filesystem->createDirectory($buildDir);
        $this->filesystem->createDirectory($masterDir);

        $this->output->writeln(sprintf('<info>Base directory</info> %s', $baseDir));
        $this->output->writeln(sprintf('<info>Build directory</info> %s', $buildDir));
        $this->output->writeln(sprintf('<info>Master directory</info> %s', $masterDir));
        $this->output->writeln(sprintf('<info>Artifact file</info> %s', $zipFile));

        chdir($baseDir);

        // Get composer.phar
        $composerPhar = sprintf('%s/composer.phar', $buildDir);

        if (file_exists($composerPhar)) {
            $this->output->writeln("Destination already exists (skipping): $composerPhar");
        } else {
            $this->output->writeln("Download composer to: $composerPhar");
            file_put_contents($composerPhar, file_get_contents('https://getcomposer.org/composer.phar'));
        }

        $this->output->writeln("Delete build/master: $masterDir");
        $this->filesystem->removeDirectory($masterDir);

        $this->output->writeln('Get master branch from git repository');
        exec("git archive --format zip --output $masterZip master", $output, $status);

        if ($status > 0) {
            throw new RuntimeException(sprintf('Download of the master branch failed. Error code: %s', $status));
        }

        if (!file_exists($masterZip)) {
            throw new RuntimeException(sprintf('File not found: %s', $masterZip));
        }

        $this->output->writeln('Unzip master branch');
        $this->filesystem->unzip($masterZip, $masterDir);

        $this->output->writeln('Install composer packages');
        exec("php $buildDir/composer.phar install --no-dev --optimize-autoloader -d $masterDir", $output, $status);

        if ($status > 0) {
            throw new RuntimeException(sprintf('The composer package installation failed. Error code: %s', $status));
        }

        $this->output->writeln('Delete master.zip');
        $this->filesystem->deleteFile($masterZip);

        $this->output->writeln('Removing unnecessary files');
        $this->filesystem->deleteFileset($masterDir, $this->fileset);

        // Zip master brunch
        $this->output->writeln("Create zip file: $zipFile");

        $this->filesystem->zipDirectory($masterDir, $zipFile);

        $this->filesystem->removeDirectory($masterDir);

        $this->output->writeln('<fg=green>Done</>');

        chdir($currentDir);
    }
}