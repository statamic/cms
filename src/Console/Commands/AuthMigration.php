<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Statamic\Console\RunsInPlease;

class AuthMigration extends Command
{
    use RunsInPlease;

    protected $composer;
    protected $name = 'statamic:auth:migration';
    protected $description = 'Generate Auth Migrations';

    public function __construct(Composer $composer)
    {
        parent::__construct();

        $this->composer = $composer;
    }

    public function handle()
    {
        $from = __DIR__.'/stubs/auth/statamic_auth_tables.php.stub';
        $file = date('Y_m_d_His', time()).'_statamic_auth_tables';
        $to = database_path("migrations/{$file}.php");

        copy($from, $to);

        $this->line("<info>Created Migration:</info> {$file}");

        $this->composer->dumpAutoloads();
    }
}
