<?php

namespace Statamic\UpdateScripts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AddTwoFactorColumns extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0')
            && config('statamic.users.repository', 'file') === 'eloquent';
    }

    public function update()
    {
        if (! $this->migrationExists('add_two_factor_columns')) {
            $stub = __DIR__.'/stubs/add_two_factor_columns.php.stub';
            $filename = date('Y_m_d_His').'_add_two_factor_columns_migration.php';

            File::put(database_path("migrations/$filename"), File::get($stub));

            $this->console->line(sprintf(
                'Migration <info>database/migrations/%s</info> created successfully.',
                $filename
            ));

            $this->console->line('Run <comment>php artisan migrate</comment> to apply the migration.');
        }

        if (! in_array('two_factor_confirmed_at', array_keys($this->model()->getCasts()))) {
            $reflection = new \ReflectionClass($this->model());
            $path = $reflection->getFileName();

            $contents = Str::of(File::get($path))
                ->replace("'preferences' => 'json'", <<<'PHP'
'preferences' => 'json',
            'two_factor_confirmed_at' => 'datetime'
PHP)
                ->replace('"preferences" => "json"', <<<'PHP'
"preferences" => "json",
            "two_factor_confirmed_at" => "datetime"
PHP);

            File::put($path, (string) $contents);
        }
    }

    protected function migrationExists(string $name): bool
    {
        return collect(File::allFiles(database_path('migrations')))
            ->map->getFilename()
            ->filter(fn (string $filename) => Str::contains($filename, $name))
            ->isNotEmpty();
    }

    protected function model(): Model
    {
        $guard = config('statamic.users.guards.cp');
        $provider = config("auth.guards.$guard.provider");

        $model = config("auth.providers.$provider.model");

        return new $model();
    }
}
