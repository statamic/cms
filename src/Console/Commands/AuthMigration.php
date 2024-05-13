<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Composer;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;

class AuthMigration extends Command
{
    use RunsInPlease;

    protected $composer;
    protected $signature = 'statamic:auth:migration {--path=}';
    protected $description = 'Generate Auth Migrations';

    public function __construct(Composer $composer)
    {
        parent::__construct();

        $this->composer = $composer;
    }

    public function handle()
    {
        $from = __DIR__.'/stubs/auth/statamic_auth_tables.php.stub';
        $file = Carbon::now()->format('Y_m_d_His').'_statamic_auth_tables';
        $to = ($path = $this->option('path')) ? $path."/{$file}.php" : database_path("migrations/{$file}.php");

        $contents = File::get($from);

        if (config('statamic.users.tables.users', 'users') === 'users') {
            $contents = str_replace('USERS_TABLE_REPLACE', File::get(__DIR__.'/stubs/auth/update_users_table.php.stub'), $contents);
        } else {
            $contents = str_replace('USERS_TABLE_REPLACE', File::get(__DIR__.'/stubs/auth/create_users_table.php.stub'), $contents);
        }

        $contents = str_replace('USERS_TABLE', config('statamic.users.tables.users', 'users'), $contents);
        $contents = str_replace('ROLE_USER_TABLE', config('statamic.users.tables.role_user', 'role_user'), $contents);
        $contents = str_replace('GROUP_USER_TABLE', config('statamic.users.tables.group_user', 'group_user'), $contents);

        File::put($to, $contents);

        $this->components->info("Migration [$file] created successfully.");

        $this->createGroupsTable();
        $this->createRolesTable();

        $this->composer->dumpAutoloads();
    }

    private function createGroupsTable()
    {
        if (config('statamic.users.tables.groups', false) == false) {
            return;
        }

        $from = __DIR__.'/stubs/auth/statamic_groups_table.php.stub';
        $file = Carbon::now()->format('Y_m_d_His').'_statamic_groups_table';
        $to = ($path = $this->option('path')) ? $path."/{$file}.php" : database_path("migrations/{$file}.php");

        $contents = File::get($from);

        $contents = str_replace('GROUPS_TABLE', config('statamic.users.tables.groups', 'groups'), $contents);

        File::put($to, $contents);

        $this->components->info("Migration [$file] created successfully.");
    }

    private function createRolesTable()
    {
        if (config('statamic.users.tables.roles', false) == false) {
            return;
        }

        $from = __DIR__.'/stubs/auth/statamic_roles_table.php.stub';
        $file = Carbon::now()->format('Y_m_d_His').'_statamic_roles_table';
        $to = ($path = $this->option('path')) ? $path."/{$file}.php" : database_path("migrations/{$file}.php");

        $contents = File::get($from);

        $contents = str_replace('ROLES_TABLE', config('statamic.users.tables.roles', 'roles'), $contents);

        File::put($to, $contents);

        $this->components->info("Migration [$file] created successfully.");
    }
}
