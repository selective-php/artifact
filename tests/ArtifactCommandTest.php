<?php

namespace Selective\Artifact\Test;

use PHPUnit\Framework\TestCase;
use Selective\Artifact\Application\ArtifactApplication;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Test.
 */
class ArtifactCommandTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testArtifact()
    {
        chdir(__DIR__ . '/..');

        $application = new ArtifactApplication();

        $command = $application->find('build');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--test' => true,
        ]);

        $display = $commandTester->getDisplay();
        $this->assertContains('Artifact Generator', $display);
        $this->assertContains('Test mode', $display);
        $this->assertNotContains('Done', $display);
    }
}
