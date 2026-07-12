<?php

namespace Statamic\Contracts\Auth;

use Statamic\Auth\UserCollection;
use Statamic\OAuth\Provider;

interface UserRepository
{
    public function make(): User;

    public function all(): UserCollection;

    public function find($id): ?User;

    public function findByEmail(string $email): ?User;

    public function findByOAuthId(Provider $provider, string $id): ?User;

    public function findOrFail($id): User;

    public function current(): ?User;

    public function fromUser($user): ?User;

    public function save(User $user);

    public function delete(User $user);
}
