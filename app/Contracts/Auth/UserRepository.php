<?php

namespace Statamic\Contracts\Auth;

use Statamic\Contracts\Auth\User;
use Statamic\Auth\UserCollection;

interface UserRepository
{
    public function make(): User;
    public function all(): UserCollection;
    // public function find($id): ?User;

    public function findByEmail(string $email): ?User;

    // current/logged in
    // oauth

    public function save(User $user);
    public function delete(User $user);
}
