<?php

namespace Statamic\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wilderborn\Partyline\Facade as Partyline;

trait EnhancesCommands
{
    public function run(InputInterface $input, OutputInterface $output): int
    {
        Partyline::bind($this);

        return parent::run($input, $output);
    }
}
