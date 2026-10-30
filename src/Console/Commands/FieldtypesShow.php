<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Exceptions\FieldtypeNotFoundException;
use Statamic\Fields\FieldtypeInspector;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class FieldtypesShow extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:fieldtypes:show
        {handle : The registered fieldtype handle}
        {--json : Output the information as JSON}';

    protected $description = 'Show configuration definitions for an installed fieldtype';

    protected $help = <<<'HELP'
Defaults describe fieldtype definitions, not an existing field's effective configuration or blueprint overrides.

Missing or unresolved options may come from the editor or project context; defaults are not an exhaustive list of choices.
HELP;

    public function handle(FieldtypeInspector $inspector): int
    {
        try {
            $data = $inspector->show($this->argument('handle'));
        } catch (FieldtypeNotFoundException $exception) {
            if ($this->option('json')) {
                $this->output->writeln(
                    json_encode(['error' => $exception->getMessage()], JSON_THROW_ON_ERROR),
                    OutputInterface::OUTPUT_RAW
                );
            } else {
                $this->components->error($exception->getMessage());
            }

            return self::FAILURE;
        }

        $this->output->writeln(
            $this->option('json')
                ? json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
                : Yaml::dump($data, 10, 2),
            OutputInterface::OUTPUT_RAW
        );

        return self::SUCCESS;
    }
}
