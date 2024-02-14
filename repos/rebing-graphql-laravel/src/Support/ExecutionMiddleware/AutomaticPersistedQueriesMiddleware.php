<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Support\ExecutionMiddleware;

use Closure;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Type\Schema;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Config\Repository;
use Rebing\GraphQL\Error\AutomaticPersistedQueriesError;
use Rebing\GraphQL\Support\OperationParams;

class AutomaticPersistedQueriesMiddleware extends AbstractExecutionMiddleware
{
    /** @var Factory */
    private $cache;
    /** @var Repository */
    private $config;

    public function __construct(Factory $cache, Repository $config)
    {
        $this->cache = $cache;
        $this->config = $config;
    }

    public function handle(string $schemaName, Schema $schema, OperationParams $params, $rootValue, $contextValue, Closure $next): ExecutionResult
    {
        $query = $params->query;

        $apqEnabled = $this->config->get('graphql.apq.enable', false);

        // Even if APQ is disabled, we keep this logic for the negotiation protocol
        $persistedQuery = $params->extensions['persistedQuery'] ?? null;

        if ($persistedQuery && !$apqEnabled) {
            throw AutomaticPersistedQueriesError::persistedQueriesNotSupported();
        }

        // APQ disabled? Nothing to be done
        if (!$apqEnabled) {
            return $next($schemaName, $schema, $params, $rootValue, $contextValue);
        }

        // No hash? Nothing to be done
        $hash = $persistedQuery['sha256Hash'] ?? null;

        if (null === $hash) {
            return $next($schemaName, $schema, $params, $rootValue, $contextValue);
        }

        $apqCacheDriver = $this->config->get('graphql.apq.cache_driver');
        $apqCachePrefix = $this->config->get('graphql.apq.cache_prefix');
        $apqCacheIdentifier = "$apqCachePrefix:$schemaName:$hash";

        $cache = $this->cache->store($apqCacheDriver);

        // store in cache
        if ($query) {
            if ($hash !== hash('sha256', $query)) {
                throw AutomaticPersistedQueriesError::invalidHash();
            }

            $parsedQuery = $params->getParsedQuery();

            $datum = [
                'query' => $query,
                'parsedQuery' => $parsedQuery,
            ];

            $ttl = $this->config->get('graphql.apq.cache_ttl', 300);
            $cache->set($apqCacheIdentifier, $datum, $ttl);

            return $next($schemaName, $schema, $params, $rootValue, $contextValue);
        }

        // retrieve from cache
        if (!$cache->has($apqCacheIdentifier)) {
            throw AutomaticPersistedQueriesError::persistedQueriesNotFound();
        }

        [
            'query' => $params->query,
            'parsedQuery' => $parsedQuery,
        ] = $cache->get($apqCacheIdentifier);

        $params->setParsedQuery($parsedQuery);

        return $next($schemaName, $schema, $params, $rootValue, $contextValue);
    }
}
