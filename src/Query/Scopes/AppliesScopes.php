<?php

namespace Statamic\Query\Scopes;

use Statamic\Facades\Scope;
use Statamic\Tags\Parameters;

trait AppliesScopes
{
    public function applyScope($scope, Parameters|array $context = [])
    {
        if (! $class = $this->getScopeClassFor($scope)) {
            throw new \Exception("The [$scope] scope does not exist.");
        }

        Scope::find($class::handle())->apply($this, $context);
    }

    public function canApplyScope($scope): bool
    {
        return (bool) $this->getScopeClassFor($scope);
    }

    private function getScopeClassFor(string $method): ?string
    {
        return app('statamic.query-scopes')
            ->get(get_class($this), collect())
            ->get($method);
    }
}
