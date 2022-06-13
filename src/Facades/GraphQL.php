<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\GraphQL\Manager;

/**
 * @method static void addField($type, $field, $closure)
 * @method static array getExtraTypeFields($type)
 * @method static void addType($type)
 * @method static void addTypes($type)
 * @method static \GraphQL\Type\Definition\Type type($type)
 * @method static \GraphQL\Type\Definition\NonNull nonNull($type)
 * @method static \GraphQL\Type\Definition\ListOfType listOf($type)
 * @method static \GraphQL\Type\Definition\ID id()
 * @method static \GraphQL\Type\Definition\IntType int()
 * @method static \GraphQL\Type\Definition\FloatType float()
 * @method static \GraphQL\Type\Definition\BooleanType boolean()
 * @method static \GraphQL\Type\Definition\Type paginate($type)
 * @method static void addQuery($query)
 * @method static array getExtraQueries()
 * @method static void addMiddleware($middleware)
 * @method static array getExtraMiddleware()
 *
 * @see \Statamic\GraphQL\Manager
 */
class GraphQL extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
