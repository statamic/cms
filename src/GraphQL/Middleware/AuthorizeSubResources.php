<?php

namespace Statamic\GraphQL\Middleware;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Validation\ValidationException;
use Rebing\GraphQL\Support\Middleware;

class AuthorizeSubResources extends Middleware
{
    public function handle($root, $args, $context, ResolveInfo $info, Closure $next)
    {
        $allowedSubResources = collect($root->allowedSubResources());

        $forbidden = collect($args[$root->subResourceArg()] ?? [])
            ->filter(fn ($resource) => ! $allowedSubResources->contains($resource));

        if ($forbidden->isNotEmpty()) {
            throw ValidationException::withMessages([
                $root->subResourceArg() => 'Forbidden: '.$forbidden->join(', '),
            ]);
        }

        return $next($root, $args, $context, $info);
    }
}
