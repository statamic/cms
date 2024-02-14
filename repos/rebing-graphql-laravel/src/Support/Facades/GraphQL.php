<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support\Facades;

use GraphQL\Error\Error;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Illuminate\Support\Facades\Facade;
use Rebing\GraphQL\GraphQL as RealGraphQL;
use Rebing\GraphQL\Support\OperationParams;

/**
 * @method static array execute(string $schemaName, OperationParams $operationParams, $rootValue = null, $contextValue = null)
 * @method static array query(string $query, ?array $params = null, array $opts = [])
 * @method static ExecutionResult queryAndReturnResult(string $query, ?array $params = null, array $opts = [])
 * @method static Type type(string $name, bool $fresh = false)
 * @method static Type paginate(string $typeName, string $customName = null)
 * @method static Type simplePaginate(string $typeName, string $customName = null)
 * @method static array<string,object|string> getTypes()
 * @method static Schema schema(?string $schema = null)
 * @method static Schema buildSchemaFromConfig(array $schemaConfig)
 * @method static array getSchemas()
 * @method static void addSchema(string $name, Schema $schema)
 * @method static void addType(object|string $class, string $name = null)
 * @method static Type objectType(ObjectType|array|string $type, array $opts = [])
 * @method static array formatError(Error $e)
 * @method static Type wrapType(string $typeName, string $customTypeName, string $wrapperTypeClass)
 */
class GraphQL extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return RealGraphQL::class;
    }
}
