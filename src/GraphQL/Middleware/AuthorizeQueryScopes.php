<?php

namespace Statamic\GraphQL\Middleware;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Validation\ValidationException;
use Rebing\GraphQL\Support\Middleware;

class AuthorizeQueryScopes extends Middleware
{
    public function handle($root, $args, $context, ResolveInfo $info, Closure $next)
    {
        $allowedScopes = collect($root->allowedScopes($args));

        $forbidden = collect($args['query_scope'] ?? [])
            ->keys()
            ->filter(fn ($filter) => ! $allowedScopes->contains($filter));

        if ($forbidden->isNotEmpty()) {
            throw ValidationException::withMessages([
                'filter' => 'Forbidden: '.$forbidden->join(', '),
            ]);
        }

        return $next($root, $args, $context, $info);
    }
}
