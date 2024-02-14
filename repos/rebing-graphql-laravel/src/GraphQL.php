<?php

declare(strict_types = 1);
namespace Rebing\GraphQL;

use Error as PhpError;
use Exception;
use GraphQL\Error\DebugFlag;
use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Server\OperationParams as BaseOperationParams;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Validation\ValidationException;
use Rebing\GraphQL\Error\AuthorizationError;
use Rebing\GraphQL\Error\ProvidesErrorCategory;
use Rebing\GraphQL\Error\ValidationError;
use Rebing\GraphQL\Exception\SchemaNotFound;
use Rebing\GraphQL\Exception\TypeNotFound;
use Rebing\GraphQL\Support\Contracts\ConfigConvertible;
use Rebing\GraphQL\Support\Contracts\TypeConvertible;
use Rebing\GraphQL\Support\ExecutionMiddleware\GraphqlExecutionMiddleware;
use Rebing\GraphQL\Support\Field;
use Rebing\GraphQL\Support\OperationParams;
use Rebing\GraphQL\Support\PaginationType;
use Rebing\GraphQL\Support\SimplePaginationType;

class GraphQL
{
    use Macroable;

    /** @var Container */
    protected $app;

    /** @var array<Schema> */
    protected $schemas = [];

    /**
     * Maps GraphQL type names to their class name.
     *
     * @var array<string,object|string>
     */
    protected $types = [];

    /** @var Type[] */
    protected $typesInstances = [];

    /** @var Repository */
    protected $config;

    public function __construct(Container $app, Repository $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function schema(?string $schemaName = null): Schema
    {
        $schemaName = $schemaName ?? $this->config->get('graphql.default_schema', 'default');

        if (isset($this->schemas[$schemaName])) {
            return $this->schemas[$schemaName];
        }

        $this->clearTypeInstances();

        $schemaConfig = static::getNormalizedSchemaConfiguration($schemaName);

        $schema = $this->buildSchemaFromConfig($schemaConfig);

        $this->addSchema($schemaName, $schema);

        return $schema;
    }

    /**
     * @param array<string,mixed>|null $variables Optional GraphQL input variables for your query/mutation
     * @param array<string,mixed> $opts Additional options, like 'schema', 'context' or 'operationName'
     * @return array<string,mixed>
     */
    public function query(string $query, ?array $variables = null, array $opts = []): array
    {
        $result = $this->queryAndReturnResult($query, $variables, $opts);

        return $this->decorateExecutionResult($result)->toArray();
    }

    /**
     * @param array<string,mixed>|null $variables Optional GraphQL input variables for your query/mutation
     * @param array<string,mixed> $opts Additional options, like 'schema', 'context' or 'operationName'
     */
    public function queryAndReturnResult(string $query, ?array $variables = null, array $opts = []): ExecutionResult
    {
        $context = $opts['context'] ?? null;
        $schemaName = $opts['schema'] ?? $this->config->get('graphql.default_schema', 'default');
        $operationName = $opts['operationName'] ?? null;
        $rootValue = $opts['rootValue'] ?? null;

        $schema = $this->schema($schemaName);

        $baseParams = new BaseOperationParams();
        $baseParams->query = $query;
        $baseParams->operation = $operationName;
        $baseParams->variables = $variables;
        $params = new OperationParams($baseParams);

        return $this->executeAndReturnResult($schemaName, $schema, $params, $rootValue, $context);
    }

    /**
     * @param mixed $rootValue
     * @param mixed $contextValue
     * @return array<string,mixed>
     */
    public function execute(string $schemaName, OperationParams $operationParams, $rootValue = null, $contextValue = null): array
    {
        $schema = $this->schema($schemaName);

        $result = $this->executeAndReturnResult($schemaName, $schema, $operationParams, $rootValue, $contextValue);

        return $this->decorateExecutionResult($result)->toArray();
    }

    /**
     * @param mixed $rootValue
     * @param mixed $contextValue
     */
    protected function executeAndReturnResult(string $schemaName, Schema $schema, OperationParams $params, $rootValue = null, $contextValue = null): ExecutionResult
    {
        try {
            $middleware = $this->executionMiddleware($schemaName);

            return $this->executeViaMiddleware($middleware, $schemaName, $schema, $params, $rootValue, $contextValue);
        } catch (Error $error) {
            return new ExecutionResult(null, [$error]);
        }
    }

    /**
     * @param array<string> $middleware
     * @param mixed $rootValue
     * @param mixed $contextValue
     */
    protected function executeViaMiddleware(array $middleware, string $schemaName, Schema $schema, OperationParams $params, $rootValue = null, $contextValue = null): ExecutionResult
    {
        return $this->app->make(Pipeline::class)
            ->send([$schemaName, $schema, $params, $rootValue, $contextValue])
            ->through($middleware)
            ->via('resolve')
            ->thenReturn();
    }

    /**
     * @return array<string>
     */
    protected function executionMiddleware(string $schemaName): array
    {
        $executionMiddleware = $schemaName
            ? $this->config->get("graphql.schemas.$schemaName.execution_middleware")
            : null;

        return $this->appendGraphqlExecutionMiddleware(
            $executionMiddleware ??
            $this->config->get('graphql.execution_middleware') ??
            []
        );
    }

    /**
     * @phpstan-param array<class-string> $middlewares
     * @phpstan-return array<class-string>
     */
    protected function appendGraphqlExecutionMiddleware(array $middlewares): array
    {
        $middlewares[] = GraphqlExecutionMiddleware::class;

        return $middlewares;
    }

    /**
     * @param array<int|string,string> $types
     */
    public function addTypes(array $types): void
    {
        foreach ($types as $name => $type) {
            $this->addType($type, is_numeric($name) ? null : $name);
        }
    }

    /**
     * @param object|string $class
     */
    public function addType($class, string $name = null): void
    {
        if (!$name) {
            $type = \is_object($class) ? $class : $this->app->make($class);
            $name = $type->name;
        }

        $this->types[$name] = $class;
    }

    public function type(string $name, bool $fresh = false): Type
    {
        $modifiers = [];

        while (true) {
            if (\Safe\preg_match('/^(.+)!$/', $name, $matches)) {
                $name = $matches[1];
                array_unshift($modifiers, 'nonNull');
            } elseif (\Safe\preg_match('/^\[(.+)]$/', $name, $matches)) {
                $name = $matches[1];
                array_unshift($modifiers, 'listOf');
            } else {
                break;
            }
        }

        $type = $this->getType($name, $fresh);

        foreach ($modifiers as $modifier) {
            $type = Type::$modifier($type);
        }

        return $type;
    }

    public function getType(string $name, bool $fresh = false): Type
    {
        $standardTypes = Type::getStandardTypes();

        if (\in_array($name, $standardTypes)) {
            return $standardTypes[$name];
        }

        if (!isset($this->types[$name])) {
            $error = "Type $name not found. Check that the config array key for the type matches the name attribute in the type's class.";

            throw new TypeNotFound($error);
        }

        if (!$fresh && isset($this->typesInstances[$name])) {
            return $this->typesInstances[$name];
        }

        $type = $this->types[$name];

        if (!\is_object($type)) {
            $type = $this->app->make($type);
        }

        $instance = $type->toType();
        $this->typesInstances[$name] = $instance;

        return $instance;
    }

    /**
     * @param ObjectType|array<int|string,class-string|array<string,mixed>>|string $type
     * @param array<string,string> $opts
     */
    public function objectType($type, array $opts = []): Type
    {
        // If it's already an ObjectType, just update properties and return it.
        // If it's an array, assume it's an array of fields and build ObjectType
        // from it. Otherwise, build it from a string or an instance.
        $objectType = null;

        if ($type instanceof ObjectType) {
            $objectType = $type;

            foreach ($opts as $key => $value) {
                if (property_exists($objectType, $key)) {
                    $objectType->{$key} = $value;
                }

                if (isset($objectType->config[$key])) {
                    $objectType->config[$key] = $value;
                }
            }
        } elseif (\is_array($type)) {
            $objectType = $this->buildObjectTypeFromFields($type, $opts);
        } else {
            $objectType = $this->buildObjectTypeFromClass($type, $opts);
        }

        return $objectType;
    }

    /**
     * @param ObjectType|string $type
     * @param array<string,string> $opts
     */
    protected function buildObjectTypeFromClass($type, array $opts = []): Type
    {
        if (!\is_object($type)) {
            $type = $this->app->make($type);
        }

        if (!$type instanceof TypeConvertible) {
            throw new TypeNotFound(
                \Safe\sprintf(
                    'Unable to convert %s to a GraphQL type, please add/implement the interface %s',
                    \get_class($type),
                    TypeConvertible::class
                )
            );
        }

        foreach ($opts as $key => $value) {
            $type->{$key} = $value;
        }

        return $type->toType();
    }

    /**
     * @param array<int|string,class-string|array<string,mixed>> $fields
     * @param array<string,string> $opts
     */
    protected function buildObjectTypeFromFields(array $fields, array $opts = []): ObjectType
    {
        $typeFields = [];

        foreach ($fields as $name => $field) {
            if (\is_string($field)) {
                $field = $this->app->make($field);
                /** @var Field $field */
                $field = $field->toArray();
            }
            $name = is_numeric($name) ? $field['name'] : $name;
            $field['name'] = $name;
            $typeFields[$name] = $field;
        }

        return new ObjectType(array_merge([
            'fields' => $typeFields,
        ], $opts));
    }

    public function addSchema(string $name, Schema $schema): void
    {
        $this->schemas[$name] = $schema;
    }

    /**
     * @param array<string,mixed> $schemaConfig
     */
    public function buildSchemaFromConfig(array $schemaConfig): Schema
    {
        $schemaQuery = $schemaConfig['query'] ?? [];
        $schemaMutation = $schemaConfig['mutation'] ?? [];
        $schemaSubscription = $schemaConfig['subscription'] ?? [];
        $schemaTypes = $schemaConfig['types'] ?? [];
        $schemaDirectives = $schemaConfig['directives'] ?? [];

        $this->addTypes($schemaTypes);

        $query = $this->objectType($schemaQuery, [
            'name' => 'Query',
        ]);

        $mutation = $schemaMutation
            ? $this->objectType($schemaMutation, ['name' => 'Mutation'])
            : null;

        $subscription = $schemaSubscription
            ? $this->objectType($schemaSubscription, ['name' => 'Subscription'])
            : null;

        $directives = Directive::getInternalDirectives();

        foreach ($schemaDirectives as $class) {
            $directive = $this->app->make($class);
            $directives[$directive->name] = $directive;
        }

        return new Schema([
            'query' => $query,
            'mutation' => $mutation,
            'subscription' => $subscription,
            'directives' => $directives,
            'types' => function () {
                $types = [];

                foreach ($this->getTypes() as $name => $type) {
                    $types[] = $this->type($name);
                }

                return $types;
            },
            'typeLoader' => function ($name) use (
                $query,
                $mutation,
                $subscription
            ) {
                return match ($name) {
                    'Query' => $query,
                    'Mutation' => $mutation,
                    'Subscription' => $subscription,
                    default => $this->type($name),
                };
            },
        ]);
    }

    public function clearType(string $name): void
    {
        if (isset($this->types[$name])) {
            unset($this->types[$name]);
        }
    }

    public function clearSchema(string $name): void
    {
        if (isset($this->schemas[$name])) {
            unset($this->schemas[$name]);
        }
    }

    public function clearTypes(): void
    {
        $this->types = [];
    }

    public function clearSchemas(): void
    {
        $this->schemas = [];
    }

    /**
     * @return array<string,object|string>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return array<Schema>
     */
    public function getSchemas(): array
    {
        return $this->schemas;
    }

    protected function clearTypeInstances(): void
    {
        $this->typesInstances = [];
    }

    public function paginate(string $typeName, string $customName = null): Type
    {
        $name = $customName ?: $typeName . 'Pagination';

        if (!isset($this->typesInstances[$name])) {
            $paginationType = $this->config->get('graphql.pagination_type', PaginationType::class);
            $this->wrapType($typeName, $name, $paginationType);
        }

        return $this->typesInstances[$name];
    }

    public function simplePaginate(string $typeName, string $customName = null): Type
    {
        $name = $customName ?: $typeName . 'SimplePagination';

        if (!isset($this->typesInstances[$name])) {
            $paginationType = $this->config->get('graphql.simple_pagination_type', SimplePaginationType::class);
            $this->wrapType($typeName, $name, $paginationType);
        }

        return $this->typesInstances[$name];
    }

    /**
     * To add customs result to the query or mutations.
     *
     * @param string $typeName The original type name
     * @param string $customTypeName The new type name
     * @param class-string<Type> $wrapperTypeClass The class to create the new type
     */
    public function wrapType(string $typeName, string $customTypeName, string $wrapperTypeClass): Type
    {
        if (!isset($this->typesInstances[$customTypeName])) {
            $wrapperClass = new $wrapperTypeClass($typeName, $customTypeName);
            $this->typesInstances[$customTypeName] = $wrapperClass;
            $this->types[$customTypeName] = $wrapperClass;
        }

        return $this->typesInstances[$customTypeName];
    }

    /**
     * @see \GraphQL\Executor\ExecutionResult::setErrorFormatter
     * @return array<string,mixed>
     */
    public static function formatError(Error $e): array
    {
        $debug = Config::get('app.debug') ? (DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE) : DebugFlag::NONE;
        $formatter = FormattedError::prepareFormatter(null, $debug);
        $error = $formatter($e);

        $previous = $e->getPrevious();

        if ($previous) {
            if ($previous instanceof ValidationException) {
                $error['message'] = 'validation';
                $error['extensions'] = [
                    'category' => 'validation',
                    'validation' => $previous->validator->errors()->getMessages(),
                ];
            }

            if ($previous instanceof ValidationError) {
                $error['extensions']['validation'] = $previous->getValidatorMessages()->getMessages();
            }

            if ($previous instanceof ProvidesErrorCategory) {
                $error['extensions']['category'] = $previous->getCategory();
            }
        } elseif ($e instanceof ProvidesErrorCategory) {
            $error['extensions']['category'] = $e->getCategory();
        }

        return $error;
    }

    /**
     * @param Error[] $errors
     * @return Error[]
     */
    public static function handleErrors(array $errors, callable $formatter): array
    {
        $handler = app()->make(ExceptionHandler::class);

        foreach ($errors as $error) {
            // Try to unwrap exception
            $error = $error->getPrevious() ?: $error;

            // Don't report certain GraphQL errors
            if ($error instanceof ValidationError ||
                $error instanceof AuthorizationError ||
                !(
                    $error instanceof Exception ||
                    $error instanceof PhpError
                )) {
                continue;
            }

            if (!$error instanceof Exception) {
                $error = new Exception(
                    $error->getMessage(),
                    $error->getCode(),
                    $error
                );
            }

            $handler->report($error);
        }

        return array_map($formatter, $errors);
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public static function getNormalizedSchemasConfiguration(): array
    {
        $schemaConfigs = [];

        /** @var string $schemaName */
        foreach (array_keys(Config::get('graphql.schemas', [])) as $schemaName) {
            $schemaConfigs[$schemaName] = static::getNormalizedSchemaConfiguration($schemaName);
        }

        return $schemaConfigs;
    }

    /**
     * @return array<string,mixed>
     */
    public static function getNormalizedSchemaConfiguration(string $schemaName): array
    {
        $schemas = Config::get('graphql.schemas');

        if (!\array_key_exists($schemaName, $schemas)) {
            throw new SchemaNotFound("No configuration for schema '$schemaName' found");
        }

        $schemaConfig = $schemas[$schemaName];

        if (!\is_string($schemaConfig) && !\is_array($schemaConfig)) {
            throw new SchemaNotFound(
                \Safe\sprintf(
                    "Configuration for schema '%s' must be either an array or a class implementing %s, found type %s",
                    $schemaName,
                    ConfigConvertible::class,
                    \gettype($schemaConfig)
                )
            );
        }

        if (!$schemaConfig) {
            throw new SchemaNotFound("Empty configuration found for schema '$schemaName'");
        }

        if (\is_string($schemaConfig)) {
            if (!class_exists($schemaConfig)) {
                throw new SchemaNotFound("Cannot find class '$schemaConfig' for schema '$schemaName'");
            }

            /** @var ConfigConvertible $instance */
            $instance = app()->make($schemaConfig);

            $schemaConfig = $instance->toConfig();
        }

        return $schemaConfig;
    }

    public function decorateExecutionResult(ExecutionResult $executionResult): ExecutionResult
    {
        $errorFormatter = $this->config->get('graphql.error_formatter', [static::class, 'formatError']);
        $errorsHandler = $this->config->get('graphql.errors_handler', [static::class, 'handleErrors']);

        return $executionResult
            ->setErrorsHandler($errorsHandler)
            ->setErrorFormatter($errorFormatter);
    }

    public function getConfigRepository(): Repository
    {
        return $this->config;
    }
}
