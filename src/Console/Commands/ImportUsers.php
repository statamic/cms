<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Statamic\Auth\Eloquent\User as EloquentUser;
use Statamic\Auth\File\User as FileUser;
use Statamic\Auth\UserRepositoryManager;
use Statamic\Console\RunsInPlease;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Auth\UserRepository as UserRepositoryContract;
use Statamic\Facades\Stache;
use Statamic\Facades\User;
use Statamic\Stache\Repositories\UserRepository as FileRepository;
use Statamic\Stache\Stores\UsersStore;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\progress;

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
            error('Your site is not using the eloquent user repository.');

            return 1;
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
            error('Please add the HasUuids trait to your '.$model.' model in order to run this importer.');

            return 1;
        }

        $store = app(UsersStore::class)->directory(config('statamic.stache.stores.users.directory', base_path('users')));
        Stache::registerStore($store);

        app()->bind(UserContract::class, FileUser::class);
        app()->bind(UserRepositoryContract::class, FileRepository::class);

        $users = User::all();

        if ($users->isEmpty()) {
            error('No users to import!');

            return 1;
        }

        app()->bind(UserContract::class, EloquentUser::class);

        $eloquentRepository = app(UserRepositoryManager::class)->createEloquentDriver([]);

        progress(
            label: 'Importing users...',
            steps: $users,
            callback: function ($user, $progress) use ($eloquentRepository) {
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
                    $eloquentUser->explicitRoles($data->get('roles'));
                }

                $eloquentUser->saveToDatabase();

                $eloquentUser->model()->forceFill(['password' => $user->password()]);
                $eloquentUser->model()->saveQuietly();
            }
        );

        info('Users imported');
    }
}
