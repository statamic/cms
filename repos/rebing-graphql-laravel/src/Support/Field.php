<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type as GraphQLType;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Rebing\GraphQL\Error\AuthorizationError;
use Rebing\GraphQL\Error\ValidationError;
use Rebing\GraphQL\Support\AliasArguments\AliasArguments;
use ReflectionMethod;

/**
 * @property string $name
 */
abstract class Field
{
    /** @var array<string,mixed> */
    protected $attributes = [];

    /** @var string[] */
    protected $middleware = [];

    /**
     * Override this in your queries or mutations
     * to provide custom authorization.
     *
     * @param mixed $root
     * @param mixed $ctx
     */
    public function authorize($root, array $args, $ctx, ResolveInfo $resolveInfo = null, Closure $getSelectFields = null): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [];
    }

    abstract public function type(): GraphQLType;

    /**
     * @return array<string,array<string,mixed>>
     */
    public function args(): array
    {
        return [];
    }

    /**
     * Define custom Laravel Validator messages as per Laravel 'custom error messages'.
     *
     * @param array $args submitted arguments
     */
    public function validationErrorMessages(array $args = []): array
    {
        return [];
    }

    /**
     * Define custom Laravel Validator attributes as per Laravel 'custom attributes'.
     *
     * @param array<string,mixed> $args submitted arguments
     * @return array<string,string>
     */
    public function validationAttributes(array $args = []): array
    {
        return [];
    }

    /**
     * @param array<string,mixed> $args
     * @return array<string,mixed>
     */
    protected function rules(array $args = []): array
    {
        return [];
    }

    /**
     * @param array<string,mixed> $arguments
     * @return array<string,mixed>
     */
    public function getRules(array $arguments = []): array
    {
        $rules = $this->rules($arguments);
        $argsRules = (new Rules($this->args(), $arguments))->get();

        return array_merge($argsRules, $rules);
    }

    /**
     * @param array<string,mixed> $arguments
     * @param array<string,mixed> $rules
     */
    protected function validateArguments(array $arguments, array $rules): void
    {
        $validator = $this->getValidator($arguments, $rules);

        if ($validator->fails()) {
            throw new ValidationError('validation', $validator);
        }
    }

    /**
     * @param array<string,mixed> $fieldsAndArgumentsSelection
     */
    public function validateFieldArguments(array $fieldsAndArgumentsSelection): void
    {
        $argsRules = (new RulesInFields($this->type(), $fieldsAndArgumentsSelection))->get();

        if (!$argsRules) {
            return;
        }

        $validator = $this->getValidator($fieldsAndArgumentsSelection, $argsRules);

        if ($validator->fails()) {
            throw new ValidationError('validation', $validator);
        }
    }

    public function getValidator(array $args, array $rules): ValidatorContract
    {
        // allow our error messages to be customised
        $messages = $this->validationErrorMessages($args);

        // allow our attributes to be customized
        $attributes = $this->validationAttributes($args);

        return Validator::make($args, $rules, $messages, $attributes);
    }

    /**
     * @return array<string>
     */
    protected function getMiddleware(): array
    {
        return $this->middleware;
    }

    protected function getResolver(): ?Closure
    {
        $resolver = $this->originalResolver();

        if (!$resolver) {
            return null;
        }

        return function ($root, ...$arguments) use ($resolver) {
            $middleware = $this->getMiddleware();

            return app()->make(Pipeline::class)
                ->send(array_merge([$this], $arguments))
                ->through($middleware)
                ->via('resolve')
                ->then(function ($arguments) use ($middleware, $resolver, $root) {
                    $result = $resolver($root, ...\array_slice($arguments, 1));

                    foreach ($middleware as $name) {
                        /** @var Middleware $instance */
                        $instance = app()->make($name);

                        if (method_exists($instance, 'terminate')) {
                            app()->terminating(function () use ($arguments, $instance, $result): void {
                                $instance->terminate($this, ...\array_slice($arguments, 1), ...[$result]);
                            });
                        }
                    }

                    return $result;
                });
        };
    }

    protected function originalResolver(): ?Closure
    {
        if (!method_exists($this, 'resolve')) {
            return null;
        }

        $resolver = [$this, 'resolve'];
        $authorize = [$this, 'authorize'];

        return function () use ($resolver, $authorize) {
            // 0 - the "root" object; `null` for queries, otherwise the parent of a type
            // 1 - the provided `args` of the query or type (if applicable), empty array otherwise
            // 2 - the `$contextValue` (usually set via a GraphQL execution middleware, e.g. `AddAuthUserContextValueMiddleware`)
            // 3 - \GraphQL\Type\Definition\ResolveInfo as provided by the underlying GraphQL PHP library
            // 4 (!) - added by this library, encapsulates creating a `SelectFields` instance
            $arguments = \func_get_args();

            // Validate mutation arguments
            $args = $arguments[1];

            $rules = $this->getRules($args);

            if ($rules) {
                $this->validateArguments($args, $rules);
            }

            $fieldsAndArguments = $arguments[3]->lookAhead()->queryPlan();

            // Validate arguments in fields
            $this->validateFieldArguments($fieldsAndArguments);

            $arguments[1] = $this->getArgs($arguments);

            // Authorize
            if (true != \call_user_func_array($authorize, $arguments)) {
                throw new AuthorizationError($this->getAuthorizationMessage());
            }

            $method = new ReflectionMethod($this, 'resolve');

            $additionalParams = \array_slice($method->getParameters(), 3);

            $additionalArguments = array_map(function ($param) use ($arguments, $fieldsAndArguments) {
                $paramType = $param->getType();

                if ($paramType->isBuiltin()) {
                    throw new InvalidArgumentException("'$param->name' could not be injected");
                }

                $className = $paramType->getName();

                if (Closure::class === $className) {
                    return function () use ($arguments, $fieldsAndArguments) {
                        return $this->instanciateSelectFields($arguments, $fieldsAndArguments);
                    };
                }

                if ($this->selectFieldClass() === $className) {
                    return $this->instanciateSelectFields($arguments, $fieldsAndArguments);
                }

                if (ResolveInfo::class === $className) {
                    return $arguments[3];
                }

                return app()->make($className);
            }, $additionalParams);

            return \call_user_func_array($resolver, array_merge(
                [$arguments[0], $arguments[1], $arguments[2]],
                $additionalArguments
            ));
        };
    }

    /**
     * @param array<int,mixed> $arguments
     * @param array<string,mixed> $fieldsAndArguments
     */
    protected function instanciateSelectFields(array $arguments, array $fieldsAndArguments): SelectFields
    {
        $ctx = $arguments[2] ?? null;

        $selectFieldsClass = $this->selectFieldClass();

        return new $selectFieldsClass($this->type(), $arguments[1], $ctx, $fieldsAndArguments);
    }

    /**
     * @return class-string<SelectFields>
     */
    protected function selectFieldClass(): string
    {
        return SelectFields::class;
    }

    protected function aliasArgs(array $arguments): array
    {
        return (new AliasArguments($this->args(), $arguments[1]))->get();
    }

    protected function getArgs(array $arguments): array
    {
        return $this->aliasArgs($arguments);
    }

    /**
     * Get the attributes from the container.
     */
    public function getAttributes(): array
    {
        $attributes = $this->attributes();

        $attributes = array_merge(
            $this->attributes,
            ['args' => $this->args()],
            $attributes
        );

        $attributes['type'] = $this->type();

        $resolver = $this->getResolver();

        if (isset($resolver)) {
            $attributes['resolve'] = $resolver;
        }

        return $attributes;
    }

    public function getAuthorizationMessage(): string
    {
        return 'Unauthorized';
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return $this->getAttributes();
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        $attributes = $this->getAttributes();

        return $attributes[$key] ?? null;
    }

    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }
}
