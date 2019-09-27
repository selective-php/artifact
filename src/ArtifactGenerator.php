<?php

namespace Selective\Artifact;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Artifact Generator.
 */
final class ArtifactGenerator
{
    /**
     * @var InputInterface
     */
    private $input;

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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param ArtifactFilesystem $filesystem
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        ArtifactFilesystem $filesystem
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->filesystem = $filesystem;
    }

    /**
     * Generate artifact file.
     *
     * @return void
     */
    public function generateArtifact()
    {
        $currentDir = (string)getcwd();
        $baseDir = (string)realpath(__DIR__ . '/..');
        $buildDir = sprintf('%s/build', $baseDir);
        $masterDir = sprintf('%s/master', $buildDir);
        $zipFile = sprintf('%s/my_app_%s.zip', $buildDir, date('YmdHis'));

        $this->output->writeln(sprintf('<info>current directory</info> %s', $currentDir));
        $this->output->writeln(sprintf('<info>using base directory</info> %s', $baseDir));
        $this->output->writeln(sprintf('<info>using build directory</info> %s', $buildDir));
        $this->output->writeln(sprintf('<info>using master directory</info> %s', $masterDir));
        $this->output->writeln(sprintf('<info>using zip file</info> %s', $zipFile));

        chdir($baseDir);

        if (!file_exists($masterDir)) {
            $this->output->writeln("Create: $masterDir");
            mkdir($masterDir, 0777, true);
        }

        //  Get composer.phar
        $composerPhar = sprintf('%s/composer.phar', $buildDir);

        if (file_exists($composerPhar)) {
            $this->output->writeln("Destination already exists (skipping): $composerPhar");
        } else {
            $this->output->writeln("Download composer to: $composerPhar");
            file_put_contents($composerPhar, file_get_contents('https://getcomposer.org/composer.phar'));
        }

        $this->output->writeln("Delete build/master: $masterDir");
        $this->filesystem->rrmdir($masterDir);

        $this->output->writeln('Get master branch from git repository');
        exec("git archive --format zip --output $buildDir/master.zip master");

        $this->output->writeln('Unzip master branch');
        $this->filesystem->unzip("$buildDir/master.zip", $masterDir);

        $this->output->writeln('Delete master.zip');
        unlink("$buildDir/master.zip");

        $this->output->writeln('Install composer packages');
        exec("php $buildDir/composer.phar install --no-dev --optimize-autoloader -d $masterDir");

        $this->output->writeln("Remove files which aren't needed on the server");
        $this->filesystem->deleteFileset($masterDir, $this->fileset);

        // Zip master brunch
        $this->output->writeln("Create zip file: $zipFile");

        if (file_exists($zipFile)) {
            unlink($zipFile);
        }

        $this->filesystem->zipDirectory($zipFile, $masterDir);

        $this->output->writeln('Done');

        chdir($currentDir);
    }
}
