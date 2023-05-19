<?php

namespace Tests\Console\Concerns;

trait SimulatesProcessErrorOutput
{
    public function simulateLoggableErrorOutput($processClass, $output)
    {
        if (! class_exists('TestProcessClass')) {
            class_alias($processClass, 'TestProcessClass');
        }

        $process = new class($output) extends \TestProcessClass
        {
            private $simulatedOutput;

            public function __construct($output)
            {
                $this->simulatedOutput = $output;
            }

            public function getCommandLine()
            {
                return 'TestProcessClass';
            }

            public function run($command, $cacheKey = null)
            {
                $this->prepareErrorOutput('err', $this->simulatedOutput);

                $this->logErrorOutput($this);
            }
        };

        $process->run('test');
    }
}
