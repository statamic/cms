<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;

abstract class Middleware
{
    /**
     * @param array<string,mixed> $args
     */
    public function handle($root, array $args, $context, ResolveInfo $info, Closure $next)
    {
        return $next($root, $args, $context, $info);
    }

    /**
     * @see Field::getResolver()  Middleware is resolved in the field resolver pipeline
     */
    public function resolve(array $arguments, Closure $next)
    {
        return $this->handle(...$arguments, ...[
            function (...$arguments) use ($next) {
                return $next($arguments);
            },
        ]);
    }
}
