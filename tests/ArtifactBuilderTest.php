<?php

namespace Selective\Artifact\Test;

use phpmock\Mock;
use phpmock\MockBuilder;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Selective\Artifact\Builder\ArtifactBuilder;
use Selective\Artifact\Compression\ZipFile;
use Selective\Artifact\Filesystem\LocalFilesystem;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\TesterTrait;

/**
 * Test.
 */
class ArtifactBuilderTest extends TestCase
{
    use TesterTrait;

    /**
     * @var string Temp path
     */
    private $temp = __DIR__ . '/temp';

    /**
     * @var Mock
     */
    private $mock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $exec = static function ($command, $output, &$status) {
            $status = 0;

            if (strpos($command, 'git archive --format zip --output') === 0) {
                copy(__DIR__ . '/data/master.zip', __DIR__ . '/temp/build/master.zip');

                return 0;
            }

            return exec($command, $output, $status);
        };

        $builder = new MockBuilder();
        $builder->setNamespace('\Selective\Artifact\Builder')
            ->setName('exec')
            ->setFunction($exec);

        $this->mock = $builder->build();
        $this->mock->enable();

        $resource = fopen('php://memory', 'w', false);

        if (!$resource) {
            throw new RuntimeException('fopen failed');
        }

        $this->output = new StreamOutput($resource);

        chdir($this->temp);
    }

    /**
     * Tear down
     *
     * @return void
     */
    public function tearDown()
    {
        $this->mock->disable();
    }

    /**
     * Create instance.
     *
     * @return ArtifactBuilder The instance
     */
    private function createBuilder(): ArtifactBuilder
    {
        $filesystem = new LocalFilesystem();
        $filesystem->removeDirectory($this->temp);
        $filesystem->createDirectory($this->temp);

        $zipFile = new ZipFile();
        $builder = new ArtifactBuilder($this->output, $filesystem, $zipFile);

        return $builder;
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testArtifactBuilder()
    {
        $this->createBuilder()->buildArtifact('test');
        $display = $this->getDisplay();

        $this->assertContains('Done', $display);
        $this->assertContains('Get master branch from git repository', $display);
        $this->assertContains('Unzip master branch', $display);
        $this->assertContains('Install composer packages', $display);
        $this->assertContains('Delete master.zip', $display);
        $this->assertContains('Removing unnecessary files', $display);
        $this->assertContains('Create zip file', $display);
        $this->assertContains('Remove directory', $display);
    }
}
