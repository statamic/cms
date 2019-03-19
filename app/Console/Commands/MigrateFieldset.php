<?php

namespace Statamic\Console\Commands;

use Statamic\API\Arr;
use Statamic\API\YAML;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;

class MigrateFieldset extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:migrate:fieldset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate v2 fieldset to blueprint';

    /**
     * Create a new controller creator command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $handle = $this->argument('handle');
        $path = $this->getPath($handle);
        $fieldset = $this->getFieldset($path, $handle);
        $blueprint = $this->migrateFieldsetToBlueprint($fieldset);

        $this->files->put($path, $blueprint);

        $this->info("Fieldset [{$handle}.yaml] has been successfully migrated to a blueprint.");
    }

    /**
     * Get path.
     *
     * @param string $handle
     * @return string
     */
    protected function getPath($handle)
    {
        return resource_path("blueprints/{$handle}.yaml");
    }

    /**
     * Get fieldset contents.
     *
     * @param string $path
     * @param string $handle
     * @return mixed
     */
    protected function getFieldset($path, $handle)
    {
        if (! $this->files->exists($path)) {
            $this->error("Cannot find fieldset [{$handle}.yaml] in resources/blueprints.");
            exit;
        }

        return $this->files->get($path);
    }

    /**
     * Migrate fieldset contents to blueprint.
     *
     * @param string $fieldset
     * @return string
     */
    protected function migrateFieldsetToBlueprint($fieldset)
    {
        $content = YAML::parse($fieldset);

        if (isset($content['fields'])) {
            $content['fields'] = $this->migrateFields($content['fields']);
        }

        if (isset($content['sections'])) {
            $content['sections'] = $this->migrateSections($content['sections']);
        }

        return YAML::dump($content);
    }

    /**
     * Migrate sections.
     *
     * @param array $sections
     * @return array
     */
    protected function migrateSections($sections)
    {
        return collect($sections)
            ->map(function ($section) {
                return Arr::set($section, 'fields', $this->migrateFields($section['fields']));
            })
            ->all();
    }

    /**
     * Migrate fields.
     *
     * @param array $fields
     * @return array
     */
    protected function migrateFields($fields)
    {
        return collect($fields)
            ->map(function ($field, $handle) {
                return ! isset($field['handle']) ? Arr::prepend($field, $handle, 'handle') : $field;
            })
            ->values()
            ->all();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['handle', InputArgument::REQUIRED, 'The fieldset handle to be migrated'],
        ];
    }
}
