<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Auth\Eloquent\Role as EloquentRole;
use Statamic\Auth\Eloquent\RoleRepository as EloquentRepository;
use Statamic\Auth\File\Role as FileRole;
use Statamic\Auth\File\RoleRepository as FileRepository;
use Statamic\Console\RunsInPlease;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Auth\RoleRepository as RoleRepositoryContract;
use Statamic\Facades\Role;

class ImportRoles extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:eloquent:import-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports file based roles into the database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! config('statamic.users.roles', false)) {
            $this->error('You do not have eloquent driven roles enabled');

            return;
        }

        $this->importRoles();

        return 0;
    }

    private function importRoles()
    {
        app()->bind(RoleContract::class, FileRole::class);
        app()->bind(RoleRepositoryContract::class, FileRepository::class);

        $roles = Role::path(config('statamic.users.paths.roles', resource_path('users/roles.yaml')))->all();

        app()->bind(RoleContract::class, EloquentRole::class);
        app()->bind(RoleRepositoryContract::class, EloquentRepository::class);

        $this->withProgressBar($roles, function ($role) {

            $eloquentRole = Role::make($role->handle())
                ->title($role->title())
                ->permissions($role->permissions());

            $eloquentRole->save();
        });

        $this->newLine();
        $this->info('Roles imported');
    }
}
