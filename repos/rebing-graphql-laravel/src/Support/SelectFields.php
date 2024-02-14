<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support;

use Closure;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type as GraphqlType;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Definition\WrappingType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class SelectFields
{
    /** @var array */
    protected $select = [];
    /** @var array */
    protected $relations = [];

    public const ALWAYS_RELATION_KEY = 'ALWAYS_RELATION_KEY';

    /**
     * @param array $queryArgs Arguments given with the query/mutation
     * @param mixed $ctx The GraphQL context; can be anything and is only passed through
     *                   Can be created/overridden by \Rebing\GraphQL\GraphQLController::queryContext
     * @param array<string,mixed> $fieldsAndArguments Field and argument tree
     */
    public function __construct(GraphqlType $parentType, array $queryArgs, $ctx, array $fieldsAndArguments)
    {
        if ($parentType instanceof WrappingType) {
            $parentType = $parentType->getInnermostType();
        }

        $requestedFields = [
            'args' => $queryArgs,
            'fields' => $fieldsAndArguments,
        ];

        /** @var array{0:mixed[],1:mixed[]} $result */
        $result = static::getSelectableFieldsAndRelations($queryArgs, $requestedFields, $parentType, null, true, $ctx);

        [$this->select, $this->relations] = $result;
    }

    /**
     * Retrieve the fields (top level) and relations that
     * will be selected with the query.
     *
     * @param array $queryArgs Arguments given with the query/mutation
     * @param mixed $ctx The GraphQL context; can be anything and is only passed through
     * @return array|Closure - if first recursion, return an array,
     *                       where the first key is 'select' array and second is 'with' array.
     *                       On other recursions return a closure that will be used in with
     */
    public static function getSelectableFieldsAndRelations(
        array $queryArgs,
        array $requestedFields,
        GraphqlType $parentType,
        ?Closure $customQuery = null,
        bool $topLevel = true,
        $ctx = null
    ) {
        $select = [];
        $with = [];

        if ($parentType instanceof WrappingType) {
            $parentType = $parentType->getInnermostType();
        }
        $parentTable = static::getTableNameFromParentType($parentType);
        $primaryKey = static::getPrimaryKeyFromParentType($parentType);

        static::handleFields($queryArgs, $requestedFields, $parentType, $select, $with, $ctx);

        // If a primary key is given, but not in the selects, add it
        if (null !== $primaryKey) {
            $primaryKey = $parentTable ? ($parentTable . '.' . $primaryKey) : $primaryKey;

            if (!\in_array($primaryKey, $select)) {
                $select[] = $primaryKey;
            }
        }

        if ($topLevel) {
            return [$select, $with];
        }

        return function ($query) use ($with, $select, $customQuery, $requestedFields, $ctx): void {
            if ($customQuery) {
                $query = $customQuery($requestedFields['args'], $query, $ctx) ?? $query;
            }

            $query->addSelect($select);
            $query->with($with);
        };
    }

    protected static function getTableNameFromParentType(GraphqlType $parentType): ?string
    {
        return isset($parentType->config['model']) ? app($parentType->config['model'])->getTable() : null;
    }

    protected static function getPrimaryKeyFromParentType(GraphqlType $parentType): ?string
    {
        return isset($parentType->config['model']) ? app($parentType->config['model'])->getKeyName() : null;
    }

    /**
     * Get the selects and withs from the given fields
     * and recurse if necessary.
     *
     * @param array $queryArgs Arguments given with the query/mutation
     * @param array<string,mixed> $requestedFields
     * @param array $select Passed by reference, adds further fields to select
     * @param array $with Passed by reference, adds further relations
     * @param mixed $ctx The GraphQL context; can be anything and is only passed through
     */
    protected static function handleFields(
        array $queryArgs,
        array $requestedFields,
        GraphqlType $parentType,
        array &$select,
        array &$with,
        $ctx
    ): void {
        $parentTable = static::isMongodbInstance($parentType) ? null : static::getTableNameFromParentType($parentType);

        foreach ($requestedFields['fields'] as $key => $field) {
            // Ignore __typename, as it's a special case
            if ('__typename' === $key) {
                continue;
            }

            // Always select foreign key
            if ($field === static::ALWAYS_RELATION_KEY) {
                static::addFieldToSelect($key, $select, $parentTable, false);

                continue;
            }

            // If field doesn't exist on definition we don't select it
            try {
                if (method_exists($parentType, 'getField')) {
                    $fieldObject = $parentType->getField($key);
                } else {
                    continue;
                }
            } catch (InvariantViolation $e) {
                continue;
            }

            $parentTypeUnwrapped = $parentType;

            if ($parentTypeUnwrapped instanceof WrappingType) {
                $parentTypeUnwrapped = $parentTypeUnwrapped->getInnermostType();
            }

            // First check if the field is even accessible
            $canSelect = static::validateField($fieldObject, $queryArgs, $ctx);

            if (true === $canSelect) {
                // Add a query, if it exists
                $customQuery = $fieldObject->config['query'] ?? null;

                // Check if the field is a relation that needs to be requested from the DB
                $queryable = static::isQueryable($fieldObject->config);

                // Pagination
                if (is_a($parentType, Config::get('graphql.pagination_type', PaginationType::class)) ||
                    is_a($parentType, Config::get('graphql.simple_pagination_type', SimplePaginationType::class))) {
                    /* @var GraphqlType $fieldType */
                    $fieldType = $fieldObject->config['type'];
                    static::handleFields(
                        $queryArgs,
                        $field,
                        $fieldType->getInnermostType(),
                        $select,
                        $with,
                        $ctx
                    );
                }
                // With

                elseif (\is_array($field['fields']) && !empty($field['fields']) && $queryable) {
                    if (isset($parentType->config['model'])) {
                        // Get the next parent type, so that 'with' queries could be made
                        // Both keys for the relation are required (e.g 'id' <-> 'user_id')
                        $relationsKey = $fieldObject->config['alias'] ?? $key;
                        $relation = \call_user_func([app($parentType->config['model']), $relationsKey]);

                        static::handleRelation($select, $relation, $parentTable, $field);

                        // New parent type, which is the relation
                        $newParentType = $parentType->getField($key)->config['type'];

                        static::addAlwaysFields($fieldObject, $field, $parentTable, true);

                        $with[$relationsKey] = static::getSelectableFieldsAndRelations(
                            $queryArgs,
                            $field,
                            $newParentType,
                            $customQuery,
                            false,
                            $ctx
                        );
                    } elseif (is_a($parentTypeUnwrapped, \GraphQL\Type\Definition\InterfaceType::class)) {
                        static::handleInterfaceFields(
                            $queryArgs,
                            $field,
                            $parentTypeUnwrapped,
                            $select,
                            $with,
                            $ctx,
                            $fieldObject,
                            $key,
                            $customQuery
                        );
                    } else {
                        static::handleFields($queryArgs, $field, $fieldObject->config['type'], $select, $with, $ctx);
                    }
                }
                // Select
                else {
                    $key = $fieldObject->config['alias']
                        ?? $key;
                    $key = $key instanceof Closure ? $key() : $key;

                    static::addFieldToSelect($key, $select, $parentTable, false);

                    static::addAlwaysFields($fieldObject, $select, $parentTable);
                }
            }
            // If privacy does not allow the field, return it as null
            elseif (null === $canSelect) {
                $fieldObject->resolveFn = function (): void {
                };
            }

            static::addAlwaysFields($fieldObject, $select, $parentTable);
        }

        // If parent type is an union or interface we select all fields
        // because we don't know which other fields are required
        if (is_a($parentType, UnionType::class) || is_a($parentType, \GraphQL\Type\Definition\InterfaceType::class)) {
            $select = ['*'];
        }
    }

    protected static function isMongodbInstance(GraphqlType $parentType): bool
    {
        $mongoType = 'Jenssegers\Mongodb\Eloquent\Model';

        return isset($parentType->config['model']) ? app($parentType->config['model']) instanceof $mongoType : false;
    }

    /**
     * @param string|Expression $field
     * @param array $select Passed by reference, adds further fields to select
     */
    protected static function addFieldToSelect($field, array &$select, ?string $parentTable, bool $forRelation): void
    {
        if ($field instanceof Expression) {
            $select[] = $field;

            return;
        }

        if ($forRelation && !\array_key_exists($field, $select['fields'])) {
            $select['fields'][$field] = [
                'args' => [],
                'fields' => true,
            ];
        } elseif (!$forRelation && !\in_array($field, $select)) {
            $field = $parentTable ? ($parentTable . '.' . $field) : $field;

            if (!\in_array($field, $select)) {
                $select[] = $field;
            }
        }
    }

    /**
     * Check the privacy status, if it's given.
     *
     * @param FieldDefinition $fieldObject Validated field
     * @param array<string, mixed> $queryArgs Arguments given with the query/mutation
     * @param mixed $ctx Query/mutation context
     *
     * @return bool|null `true`  if selectable
     *                   `false` if not selectable, but allowed
     *                   `null`  if not allowed
     */
    protected static function validateField(FieldDefinition $fieldObject, array $queryArgs, $ctx): ?bool
    {
        $selectable = true;

        // If not a selectable field
        if (isset($fieldObject->config['selectable']) && false === $fieldObject->config['selectable']) {
            $selectable = false;
        }

        if (isset($fieldObject->config['privacy'])) {
            $privacyClass = $fieldObject->config['privacy'];

            switch ($privacyClass) {
                // If privacy given as a closure
                case \is_callable($privacyClass):
                    if (false === $privacyClass($queryArgs, $ctx)) {
                        $selectable = null;
                    }

                    break;

                    // If Privacy class given
                case \is_string($privacyClass):
                    /** @var Privacy $instance */
                    $instance = app($privacyClass);

                    if (false === $instance->fire($queryArgs, $ctx)) {
                        $selectable = null;
                    }

                    break;

                default:
                    throw new RuntimeException(
                        \Safe\sprintf(
                            "Unsupported use of 'privacy' configuration on field '%s'.",
                            $fieldObject->name
                        )
                    );
            }
        }

        return $selectable;
    }

    /**
     * Determines whether the fieldObject is queryable.
     */
    protected static function isQueryable(array $fieldObject): bool
    {
        return ($fieldObject['is_relation'] ?? true) === true;
    }

    /**
     * @param Relation $relation
     * @param array $field
     */
    protected static function handleRelation(array &$select, $relation, ?string $parentTable, &$field): void
    {
        // Add the foreign key here, if it's a 'belongsTo'/'belongsToMany' relation
        if (method_exists($relation, 'getForeignKey')) {
            $foreignKey = $relation->getForeignKey();
        } elseif (method_exists($relation, 'getQualifiedForeignPivotKeyName')) {
            $foreignKey = $relation->getQualifiedForeignPivotKeyName();
        } else {
            /** @var BelongsTo|HasManyThrough|HasOneOrMany $relation */
            $foreignKey = $relation->getQualifiedForeignKeyName();
        }
        $foreignKey = $parentTable ? ($parentTable . '.' . \Safe\preg_replace(
            '/^' . preg_quote($parentTable, '/') . '\./',
            '',
            $foreignKey
        )) : $foreignKey;

        if (is_a($relation, MorphTo::class)) {
            $foreignKeyType = $relation->getMorphType();
            $foreignKeyType = $parentTable ? ($parentTable . '.' . $foreignKeyType) : $foreignKeyType;

            if (!\in_array($foreignKey, $select)) {
                $select[] = $foreignKey;
            }

            if (!\in_array($foreignKeyType, $select)) {
                $select[] = $foreignKeyType;
            }
        } elseif (is_a($relation, BelongsTo::class)) {
            if (!\in_array($foreignKey, $select)) {
                $select[] = $foreignKey;
            }
        } // If 'HasMany', then add it in the 'with'
        elseif ((is_a($relation, HasMany::class) || is_a($relation, MorphMany::class) || is_a(
            $relation,
            HasOne::class
        ) || is_a($relation, MorphOne::class)) &&
            !\array_key_exists($foreignKey, $field)) {
            $segments = explode('.', $foreignKey);
            $foreignKey = end($segments);

            if (!\array_key_exists($foreignKey, $field)) {
                $field['fields'][$foreignKey] = static::ALWAYS_RELATION_KEY;
            }

            if (is_a($relation, MorphMany::class) || is_a($relation, MorphOne::class)) {
                $field['fields'][$relation->getMorphType()] = static::ALWAYS_RELATION_KEY;
            }
        }
    }

    /**
     * Add selects that are given by the 'always' attribute.
     *
     * @param array $select Passed by reference, adds further fields to select
     */
    protected static function addAlwaysFields(
        FieldDefinition $fieldObject,
        array &$select,
        ?string $parentTable,
        bool $forRelation = false
    ): void {
        if (isset($fieldObject->config['always'])) {
            $always = $fieldObject->config['always'];

            if (\is_string($always)) {
                $always = explode(',', $always);
            }

            // Get as 'field' => true
            foreach ($always as $field) {
                static::addFieldToSelect($field, $select, $parentTable, $forRelation);
            }
        }
    }

    /**
     * @param mixed $ctx
     */
    protected static function handleInterfaceFields(
        array $queryArgs,
        array $field,
        GraphqlType $parentType,
        array &$select,
        array &$with,
        $ctx,
        FieldDefinition $fieldObject,
        string $key,
        ?Closure $customQuery
    ): void {
        $relationsKey = Arr::get($fieldObject->config, 'alias', $key);

        $with[$relationsKey] = function ($query) use (
            $queryArgs,
            $field,
            $parentType,
            &$select,
            $ctx,
            $customQuery,
            $key,
            $fieldObject
        ) {
            $parentTable = static::isMongodbInstance($parentType) ? null : static::getTableNameFromParentType($parentType);

            static::handleRelation($select, $query, $parentTable, $field);

            // New parent type, which is the relation
            try {
                if (method_exists($parentType, 'getField')) {
                    $newParentType = $parentType->getField($key)->config['type'];
                    $customQuery = $parentType->getField($key)->config['query'] ?? $customQuery;
                } else {
                    return $query;
                }
            } catch (InvariantViolation $e) {
                return $query;
            }

            static::addAlwaysFields($fieldObject, $field, $parentTable, true);

            // Find the type of the current relation by comparing table names
            if (isset($parentType->config['types'])) {
                $typesFiltered = array_filter(
                    $parentType->config['types'](),
                    function (GraphqlType $type) use ($query) {
                        /* @var Relation $query */
                        return app($type->config['model'])->getTable() === $query->getParent()->getTable();
                    }
                );
                $typesFiltered = array_values($typesFiltered ?? []);

                if (1 === \count($typesFiltered)) {
                    /* @var GraphqlType $type */
                    $type = $typesFiltered[0];
                    $relationField = $type->getField($key);
                    $newParentType = $relationField->config['type'];
                    // If a custom query is available on the selected type, it should replace the interface's one
                    $customQuery = $relationField->config['query'] ?? $customQuery;
                }
            }

            if ($newParentType instanceof WrappingType) {
                $newParentType = $newParentType->getInnermostType();
            }

            /** @var callable $callable */
            $callable = static::getSelectableFieldsAndRelations(
                $queryArgs,
                $field,
                $newParentType,
                $customQuery,
                false,
                $ctx
            );

            return $callable($query);
        };
    }

    public function getSelect(): array
    {
        return $this->select;
    }

    public function getRelations(): array
    {
        return $this->relations;
    }
}
