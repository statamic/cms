<?php

namespace Statamic\Auth;

use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Auth\UserRepository as RepositoryContract;
use Statamic\Data\StoresComputedFieldCallbacks;
use Statamic\Events\UserBlueprintFound;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\OAuth\Provider;
use Statamic\Query\Scopes\AllowsScopes;
use Statamic\Statamic;

abstract class UserRepository implements RepositoryContract
{
    use AllowsScopes, StoresComputedFieldCallbacks;

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
        if (Blink::has($blink = 'user-blueprint')) {
            return Blink::get($blink);
        }

        $blueprint = Blueprint::find('user') ?? Blueprint::makeFromFields([
            'name' => ['type' => 'text', 'display' => __('Name'), 'listable' => true],
            'email' => ['type' => 'text', 'input_type' => 'email', 'display' => __('Email Address'), 'listable' => true],
        ])->setHandle('user');

        $blueprint->ensureField('email', ['type' => 'text', 'input_type' => 'email', 'display' => __('Email Address'), 'listable' => true]);

        if (Statamic::pro()) {
            $blueprint->ensureField('roles', ['type' => 'user_roles', 'mode' => 'select', 'width' => 50, 'listable' => true, 'filterable' => false]);
            $blueprint->ensureField('groups', ['type' => 'user_groups', 'mode' => 'select', 'width' => 50, 'listable' => true, 'filterable' => false]);
        } else {
            $blueprint->removeField('roles');
            $blueprint->removeField('groups');
        }

        $blueprint->ensureField('two_factor', [
            'type' => 'two_factor',
            'display' => __('Two Factor Authentication'),
            'hide_display' => true,
        ]);

        Blink::put($blink, $blueprint);

        UserBlueprintFound::dispatch($blueprint);

        return $blueprint;
    }

    public function findByOAuthId(Provider $provider, string $id): ?User
    {
        return $this->find(
            $provider->getUserId($id)
        );
    }
}
