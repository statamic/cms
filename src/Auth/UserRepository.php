<?php

namespace Statamic\Auth;

use Statamic\OAuth\Provider;
use Statamic\Facades\Blueprint;
use Statamic\Contracts\Auth\User;
use Illuminate\Support\Collection;
use Statamic\Events\UserBlueprintFound;
use WhiteCube\Lingua\Service as Lingua;
use Statamic\Contracts\Auth\UserRepository as RepositoryContract;

abstract class UserRepository implements RepositoryContract
{
    public function create()
    {
        // TODO: Factory?
        throw new \Exception('Factory not supported. Use User::make() to get an instance.');

        return app(UserFactory::class);
    }

    public function make(): User
    {
        return app(User::class);
    }

    public function current(): ?User
    {
        if (! $user = auth()->user()) {
            return null;
        }

        return $this->fromUser($user);
    }

    public function count()
    {
        return $this->query()->count();
    }

    public function roleRepository()
    {
        return app($this->roleRepository)->path(
            $this->config['paths']['roles'] ?? resource_path('users/roles.yaml')
        );
    }

    public function userGroupRepository()
    {
        return app($this->userGroupRepository)->path(
            $this->config['paths']['groups'] ?? resource_path('users/groups.yaml')
        );
    }

    public function blueprint()
    {
        $blueprint = Blueprint::find('user') ?? Blueprint::makeFromFields([
            'name' => ['type' => 'text', 'display' => 'Name'],
            'email' => ['type' => 'text', 'input_type' => 'email', 'display' => 'Email Address'],
            'roles' => ['type' => 'user_roles', 'mode' => 'select', 'width' => 50],
            'groups' => ['type' => 'user_groups', 'mode' => 'select', 'width' => 50],
        ])->setHandle('user');

        UserBlueprintFound::dispatch($blueprint);

        return $blueprint;
    }

    public function findByOAuthId(string $provider, string $id): ?User
    {
        return $this->find(
            (new Provider($provider))->getUserId($id)
        );
    }

    public function locales(): Collection
    {
        return collect(config('statamic.users.locales'))
            ->prepend(config('app.locale'))
            ->mapWithKeys(function ($locale) {
                return [
                    $locale => [
                        'locale' => $locale,
                        'name' => ucfirst(Lingua::create($locale)->toName()),
                    ]
                ];
            });
    }
}
