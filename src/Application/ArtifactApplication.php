<?php

namespace Selective\Artifact\Application;

use Selective\Artifact\Command\BuildCommand;
use Symfony\Component\Console\Application;

/**
 * Console application.
 */
final class ArtifactApplication extends Application
{
    /**
     * Class Constructor.
     *
     * Initialize the console application.
     */
    public function __construct()
    {
        parent::__construct('Artifact Builder', '0.2.0');

        $this->addCommands([
            new BuildCommand(),
        ]);
    }
}
