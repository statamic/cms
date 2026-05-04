<?php

namespace Statamic\GraphQL\Queries;

use Closure;
use Facades\Statamic\GraphQL\TypeRegistrar;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\Query as BaseQuery;
use Statamic\GraphQL\Middleware\ResolveSite;

abstract class Query extends BaseQuery
{
    protected static $auth;

    public function __construct()
    {
        TypeRegistrar::register();
    }

    public static function auth($closure)
    {
        static::$auth = $closure;
    }

    public function authorize($root, array $args, $ctx, ?ResolveInfo $resolveInfo = null, ?Closure $getSelectFields = null): bool
    {
        if (static::$auth) {
            return call_user_func_array(static::$auth, [$root, $args, $ctx, $resolveInfo, $getSelectFields]);
        }

        return true;
    }

    protected function getMiddleware(): array
    {
        return array_merge(
            [ResolveSite::class],
            $this->middleware
        );
    }
}
