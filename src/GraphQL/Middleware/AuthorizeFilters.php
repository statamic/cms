<?php

namespace Statamic\GraphQL\Middleware;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Validation\ValidationException;
use Rebing\GraphQL\Support\Middleware;

class AuthorizeFilters extends Middleware
{
    public function handle($root, $args, $context, ResolveInfo $info, Closure $next)
    {
        $allowedFilters = collect($root->allowedFilters($args));

        $forbidden = collect($args['filter'] ?? [])
            ->keys()
            ->filter(fn ($filter) => ! $allowedFilters->contains($filter));

        if ($forbidden->isNotEmpty()) {
            throw ValidationException::withMessages([
                'filter' => 'Forbidden: '.$forbidden->join(', '),
            ]);
        }

        return $next($root, $args, $context, $info);
    }
}
