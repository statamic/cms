<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Facade;
use Statamic\Auth\Eloquent\UserGroup as EloquentGroup;
use Statamic\Auth\Eloquent\UserGroupRepository as EloquentRepository;
use Statamic\Auth\File\UserGroup as FileGroup;
use Statamic\Auth\File\UserGroupRepository as FileRepository;
use Statamic\Console\RunsInPlease;
use Statamic\Contracts\Auth\UserGroup as GroupContract;
use Statamic\Contracts\Auth\UserGroupRepository as GroupRepositoryContract;
use Statamic\Facades\UserGroup;

use function Laravel\Prompts\error;
use function Laravel\Prompts\progress;

class ImportGroups extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:eloquent:import-groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports file based groups into the database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! config('statamic.users.tables.groups', false)) {
            error('You do not have eloquent driven groups enabled');

            return;
        }

        $this->importGroups();

        return 0;
    }

    private function importGroups()
    {
        app()->bind(GroupContract::class, FileGroup::class);
        app()->bind(GroupRepositoryContract::class, FileRepository::class);

        Facade::clearResolvedInstance(GroupContract::class);
        Facade::clearResolvedInstance(GroupRepositoryContract::class);

        $groups = UserGroup::path(config('statamic.users.paths.groups', resource_path('users/groups.yaml')))->all();

        app()->bind(GroupContract::class, EloquentGroup::class);
        app()->bind(GroupRepositoryContract::class, EloquentRepository::class);

        Facade::clearResolvedInstance(GroupContract::class);
        Facade::clearResolvedInstance(GroupRepositoryContract::class);

        progress(
            label: 'Importing groups...',
            steps: $groups,
            callback: function ($group, $progress) {
                $eloquentGroup = UserGroup::make()
                    ->handle($group->handle())
                    ->title($group->title())
                    ->roles($group->roles())
                    ->data($group->data()->except(['title', 'roles']));

                $eloquentGroup->save();
            }
        );

        $this->info('Groups imported');
    }
}
