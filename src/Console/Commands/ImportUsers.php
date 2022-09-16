<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Auth\Eloquent\User as EloquentUser;
use Statamic\Auth\UserRepositoryManager;
use Statamic\Auth\File\User as FileUser;
use Statamic\Console\RunsInPlease;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Auth\UserRepository as UserRepositoryContract;
use Statamic\Facades\User;
use Statamic\Stache\Repositories\UserRepository as FileRepository;

class ImportUsers extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:eloquent:import-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports file based users into the database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (config('statamic.users.repository') === 'eloquent') {
            $this->importUsers();
        }

        return 0;
    }

    private function importUsers()
    {
        if ($this->confirm('This will import all your file users to your database, but it will not update any references to user IDs in your entry data. Do you wish to continue?')) {

            app()->bind(UserContract::class, FileUser::class);
            app()->bind(UserRepositoryContract::class, FileRepository::class);

            $users = User::all();

            app()->bind(UserContract::class, EloquentUser::class);

            $eloquentRepository = app(UserRepositoryManager::class)->createEloquentDriver([]);

            $this->withProgressBar($users, function ($user) use($eloquentRepository) {

                $data = $user->data();

                $eloquentUser = $eloquentRepository->make()
                    //->id($user->id()) - if you are using UUIDs for users, then uncomment this
                    ->email($user->email())
                    ->password($user->password())
                    ->preferences($user->preferences())
                    ->data($data->except(['groups', 'roles']));

                if ($user->isSuper()) {
                    $eloquentUser->makeSuper();
                }

                if (count($data->get('groups', [])) > 0) {
                    $eloquentUser->groups($data->get('groups'));
                }

                if (count($data->get('roles', [])) > 0) {
                    $eloquentUser->roles($data->get('roles'));
                }

                $eloquentUser->saveToDatabase();
            });

            $this->newLine();
            $this->info('Users imported');

        }
    }
}
