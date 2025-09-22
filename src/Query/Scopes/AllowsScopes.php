<?php

namespace Statamic\Query\Scopes;

use Statamic\Support\Str;

trait AllowsScopes
{
    public function allowQueryScope(string $scope, ?string $method = null)
    {
        /** @var \Illuminate\Support\Collection $scopes */
        $scopes = app('statamic.query-scopes');

        $method ??= Str::camel($scope::handle());

        $scopes->put(
            $class = get_class($this->query()),
            $scopes->get($class, collect())->put($method, $scope)
        );
    }
}
