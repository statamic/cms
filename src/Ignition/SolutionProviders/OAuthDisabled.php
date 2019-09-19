<?php

namespace Statamic\Ignition\SolutionProviders;

use Throwable;
use Statamic\Ignition\Solutions\EnableOAuth;
use Facade\IgnitionContracts\HasSolutionsForThrowable;

class OAuthDisabled implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        return $throwable->getMessage() === 'Route [statamic.oauth.login] not defined.';
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [new EnableOAuth];
    }
}
