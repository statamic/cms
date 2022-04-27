<?php

namespace Statamic\Query\Scopes;

use Statamic\Facades\Scope;

trait AppliesScopes
{
    public function applyScope($method, $context = [])
    {
        // Throw an exception if the scope doesn't exist.
        if (! $scope = Scope::find(snake_case($method))) {
            throw new \Exception("The [$method] scope does not exist.");
        }

        // Apply the scope to all builders if none were defined.
        if ($scope->builders()->isEmpty()) {
            return $scope->apply($this, $context);
        }

        // Only apply the scope to the defined builders.
        if ($scope->builders()->contains(get_class($this))) {
            return $scope->apply($this, $context);
        }

        // Throw an exception if a user is trying to access a scope that is not supported by this builder.
        throw new \Exception("The [" . get_class($this) . "] query builder does not support the [$method] scope.");
    }
}
