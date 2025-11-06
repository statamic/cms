<?php

namespace Statamic\ErrorSolutions\SolutionProviders;

use Spatie\ErrorSolutions\Contracts\HasSolutionsForThrowable;
use Statamic\ErrorSolutions\Solutions\EnableOAuth;
use Throwable;

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
