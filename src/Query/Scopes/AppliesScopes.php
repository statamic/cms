<?php

namespace Statamic\Query\Scopes;

use Statamic\Support\Str;
use Statamic\Facades\Scope;
use Statamic\Tags\Parameters;

trait AppliesScopes
{
    public function applyScope($method, Parameters|array $context = [])
    {
        // Throw an exception if the scope doesn't exist.
        if (! $scope = Scope::find(Str::snake($method))) {
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
        throw new \Exception('The ['.get_class($this)."] query builder does not support the [$method] scope.");
    }

    public function canApplyScope($method): bool
    {
        // If the scope doesn't exist, return false.
        if (! $scope = Scope::find(Str::snake($method))) {
            return false;
        }

        // If no builders are defined, return true.
        if ($scope->builders()->isEmpty()) {
            return true;
        }

        // If builders are defined and this builder is one of them, return true.
        if ($scope->builders()->contains(get_class($this))) {
            return true;
        }

        return false;
    }
}
