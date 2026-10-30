<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Fields\FieldtypeInspector;
use Symfony\Component\Console\Output\OutputInterface;

class FieldtypesList extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:fieldtypes:list {--json : Output the information as JSON}';

    protected $description = 'List the installed fieldtypes';

    public function handle(FieldtypeInspector $inspector): int
    {
        $data = $inspector->all();

        if ($this->option('json')) {
            $this->output->writeln(
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                OutputInterface::OUTPUT_RAW
            );

            return self::SUCCESS;
        }

        $this->components->info('Statamic '.$data['statamic_version']);

        $this->table(['Handle', 'Title', 'Selectable', 'Class'], array_map(fn ($fieldtype) => [
            $fieldtype['handle'],
            $fieldtype['title'],
            $fieldtype['selectable'] ? 'Yes' : 'No',
            $fieldtype['class'],
        ], $data['fieldtypes']));

        return self::SUCCESS;
    }
}
