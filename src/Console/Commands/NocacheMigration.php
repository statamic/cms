<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Composer;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;

class NocacheMigration extends Command
{
    use RunsInPlease;

    protected $composer;
    protected $signature = 'statamic:nocache:migration {--path=}';
    protected $description = 'Generate Nocache Migrations';

    public function __construct(Composer $composer)
    {
        parent::__construct();

        $this->composer = $composer;
    }

    public function handle()
    {
        $from = __DIR__.'/stubs/statamic_nocache_tables.php.stub';
        $file = Carbon::now()->format('Y_m_d_His').'_statamic_nocache_tables';
        $to = ($path = $this->option('path')) ? $path."/{$file}.php" : database_path("migrations/{$file}.php");

        $contents = File::get($from);

        $contents = str_replace('NOCACHE_TABLE', 'nocache_regions', $contents);

        File::put($to, $contents);

        $this->components->info("Migration [$file] created successfully.");

        $this->composer->dumpAutoloads();
    }
}
