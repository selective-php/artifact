<?php

namespace Selective\Artifact\Builder;

use Selective\Artifact\Compression\ZipFileInterface;
use Selective\Artifact\Filesystem\FilesystemInterface;
use Selective\Artifact\Utility\ArtifactException;
use Selective\Artifact\Utility\TextFormatter;
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
     * @var FilesystemInterface The filesystem
     */
    private $filesystem;

    /**
     * @var ZipFileInterface
     */
    private $zipFile;

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
        // Directories
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
     * @param FilesystemInterface $filesystem The filesystem
     * @param ZipFileInterface $zipFile The ZIP file helper
     */
    public function __construct(
        OutputInterface $output,
        FilesystemInterface $filesystem,
        ZipFileInterface $zipFile
    ) {
        $this->output = $output;
        $this->filesystem = $filesystem;
        $this->zipFile = $zipFile;
    }

    /**
     * Generate artifact file.
     *
     * @param string $name The artifact name
     *
     * @return void
     */
    public function buildArtifact(string $name)
    {
        $name = TextFormatter::underscore($name);
        $currentDir = $this->filesystem->normalizePath((string)getcwd());
        $baseDir = $this->filesystem->normalizePath($currentDir);
        $buildDir = $this->filesystem->normalizePath(sprintf('%s/build', $baseDir));
        $masterDir = $this->filesystem->normalizePath(sprintf('%s/master', $buildDir));
        $masterZip = "$buildDir/master.zip";

        $this->filesystem->createDirectory($buildDir);
        $this->filesystem->createDirectory($buildDir);
        $this->filesystem->createDirectory($masterDir);

        $this->output->writeln(sprintf('<info>Base directory</info> %s', $baseDir));
        $this->output->writeln(sprintf('<info>Build directory</info> %s', $buildDir));
        $this->output->writeln(sprintf('<info>Master directory</info> %s', $masterDir));

        chdir($baseDir);

        // Get composer.phar
        $this->downloadComposer($buildDir);

        $this->output->writeln("Delete build/master: $masterDir");
        $this->filesystem->removeDirectory($masterDir);

        $this->output->writeln('Get master branch from git repository');
        $this->getMasterArchive($masterZip);

        $this->output->writeln('Unzip master branch');
        $this->zipFile->extractToDirectory($masterZip, $masterDir);

        $version = $this->findVersion($masterDir);
        $zipFilePath = $this->filesystem->normalizePath(sprintf(
            '%s/%s_%s_%s.zip',
            $buildDir,
            $name,
            TextFormatter::flatVersion($version ?: '0.0.0'),
            date('YmdHis')
        ));

        $this->output->writeln('Install composer packages');
        $this->installComposerPackages($buildDir, $masterDir);

        $this->output->writeln('Delete master.zip');
        $this->filesystem->deleteFile($masterZip);

        $this->output->writeln('Removing unnecessary files');
        $this->filesystem->deleteFileset($masterDir, $this->fileset);

        // Zip master brunch
        $this->output->writeln("Create zip file: $zipFilePath");
        $this->zipFile->createFromDirectory($masterDir, $zipFilePath);

        $this->output->writeln("Remove directory: $masterDir");
        $this->filesystem->removeDirectory($masterDir);

        $this->output->writeln(sprintf('<info>Artifact file</info> %s', $zipFilePath));
        $this->output->writeln('<fg=green>Done</>');

        chdir($currentDir);
    }

    /**
     * Download composer phar.
     *
     * @param string $buildDir The destination directory
     *
     * @return void
     */
    private function downloadComposer(string $buildDir)
    {
        // Get composer.phar
        $composerPhar = sprintf('%s/composer.phar', $buildDir);

        if (file_exists($composerPhar)) {
            $this->output->writeln("Destination already exists (skipping): $composerPhar");
        } else {
            $this->output->writeln("Download composer to: $composerPhar");
            file_put_contents($composerPhar, file_get_contents('https://getcomposer.org/composer.phar'));
        }
    }

    /**
     * Get master archive.
     *
     * @param string $masterZip The destination zip file
     *
     * @throws ArtifactException
     *
     * @return void
     */
    private function getMasterArchive(string $masterZip)
    {
        exec("git archive --format zip --output $masterZip master", $output, $status);

        if ($status > 0) {
            throw new ArtifactException(sprintf('Download of the master branch failed. Error code: %s', $status));
        }

        if (!file_exists($masterZip)) {
            throw new ArtifactException(sprintf('File not found: %s', $masterZip));
        }
    }

    /**
     * Install composer packages.
     *
     * @param string $buildDir The build directory
     * @param string $masterDir The master directory
     *
     * @throws ArtifactException
     *
     * @return void
     */
    private function installComposerPackages(string $buildDir, string $masterDir)
    {
        exec("php $buildDir/composer.phar install --no-dev --optimize-autoloader -d $masterDir", $output, $status);

        if ($status > 0) {
            throw new ArtifactException(sprintf('The composer package installation failed. Error code: %s', $status));
        }
    }

    /**
     * Find application version.
     *
     * @param string $masterDir The master directory
     *
     * @return string The version
     */
    private function findVersion(string $masterDir): string
    {
        $composerFile = sprintf('%s/composer.json', $masterDir);

        if (!file_exists($composerFile)) {
            return '';
        }

        $composerConfig = json_decode(file_get_contents($composerFile), false);

        return $composerConfig->version ?? '';
    }
}
