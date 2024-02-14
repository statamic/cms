<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Error;

use GraphQL\Error\Error;

class AuthorizationError extends Error implements ProvidesErrorCategory
{
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'authorization';
    }
}
