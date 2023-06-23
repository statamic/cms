<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Schema;
use Statamic\Auth\Eloquent\User as EloquentUser;
use Statamic\Auth\File\User as FileUser;
use Statamic\Auth\UserRepositoryManager;
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
        if (config('statamic.users.repository') !== 'eloquent') {
            $this->error('Your site is not using the eloquent user repository.');

            return 0;
        }

        $this->importUsers();

        return 0;
    }

    private function importUsers()
    {
        $guard = config('statamic.users.guards.cp');
        $provider = config("auth.guards.$guard.provider");
        $model = config("auth.providers.$provider.model");

        if (! in_array(HasUuids::class, class_uses_recursive($model))) {
            $this->error('Your user model must use the HasUuids trait for this migration to run');

            return;
        }

        if (! in_array(Schema::getColumnType('users', 'id'), ['guid', 'string'])) {
            $this->error('Your users table must use UUIDs for ids in order for this migration to run');

            return;
        }

        app()->bind(UserContract::class, FileUser::class);
        app()->bind(UserRepositoryContract::class, FileRepository::class);

        $users = User::all();

        app()->bind(UserContract::class, EloquentUser::class);

        $eloquentRepository = app(UserRepositoryManager::class)->createEloquentDriver([]);

        $this->withProgressBar($users, function ($user) use ($eloquentRepository) {
            $data = $user->data();

            $eloquentUser = $eloquentRepository->make()
                ->email($user->email())
                ->preferences($user->preferences())
                ->data($data->except(['groups', 'roles'])->merge(['name' => $user->name()]))
                ->id($user->id());

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

            $eloquentUser->model()->forceFill(['password' => $user->password()]);
            $eloquentUser->model()->saveQuietly();
        });

        $this->newLine();
        $this->info('Users imported');
    }
}
