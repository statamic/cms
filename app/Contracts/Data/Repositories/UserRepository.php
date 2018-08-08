<?php

namespace Statamic\Contracts\Data\Repositories;

use Statamic\Contracts\Data\Users\User;
use Statamic\Data\Users\UserCollection;

interface UserRepository
{
    public function all(): UserCollection;
    public function find($id): ?User;
}
