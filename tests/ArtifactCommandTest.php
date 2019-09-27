<?php

namespace Selective\Artifact\Test;

use PHPUnit\Framework\TestCase;
use Selective\Artifact\ArtifactCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \Odan\Migration\Command\GenerateCommand
 */
class ArtifactCommandTest extends TestCase
{
    /**
     * Test.
     */
    public function testArtifact()
    {
        $application = new Application();
        $application->add(new ArtifactCommand());

        $command = $application->find('artifact');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $display = $commandTester->getDisplay();
        $this->assertContains('Artifact Generator', $display);
        $this->assertNotContains('using', $display);
    }
}
