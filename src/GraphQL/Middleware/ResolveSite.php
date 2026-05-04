<?php

namespace Statamic\GraphQL\Middleware;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\Middleware;
use Statamic\Facades\Site;

class ResolveSite extends Middleware
{
    public function handle($root, array $args, $context, ResolveInfo $info, Closure $next)
    {
        $site = $args['site'] ?? Site::default()->handle();
        Site::setCurrent($site);

        return $next($root, $args, $context, $info);
    }
}
