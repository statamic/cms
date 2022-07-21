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

    public function checkLine($message)
    {
        $this->line("<info>[✓]</info> $message");
    }

    public function checkInfo($message)
    {
        $this->info("[✓] $message");
    }

    public function crossLine($message)
    {
        $this->line("<fg=red>[✗]</> $message");
    }
}
