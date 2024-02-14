<?php

declare(strict_types = 1);

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Routing\Router;
use Rebing\GraphQL\GraphQL;
use Rebing\GraphQL\GraphQLController;

/** @var Repository $config */
$config = Container::getInstance()->make(Repository::class);

$routeConfig = $config->get('graphql.route');

if ($routeConfig) {
    /** @var Router $router */
    $router = app('router');

    $routeGroupAttributes = array_merge(
        [
            'prefix' => $routeConfig['prefix'] ?? 'graphql',
            'middleware' => $routeConfig['middleware'] ?? [],
        ],
        $routeConfig['group_attributes'] ?? []
    );

    $router->group(
        $routeGroupAttributes,
        function (Router $router) use ($config, $routeConfig): void {
            $schemas = GraphQL::getNormalizedSchemasConfiguration();
            $defaultSchema = $config->get('graphql.default_schema', 'default');

            foreach ($schemas as $schemaName => $schemaConfig) {
                $method = $schemaConfig['method'] ?? ['GET', 'POST'];
                $actions = array_filter([
                    'uses' => $schemaConfig['controller'] ?? $routeConfig['controller'] ?? GraphQLController::class . '@query',
                    'middleware' => $schemaConfig['middleware'] ?? $routeConfig['middleware'] ?? null,
                ]);

                // Support array syntax: `[Some::class, 'method']`
                if (\is_array($actions['uses']) && isset($actions['uses'][0], $actions['uses'][1])) {
                    $actions['uses'] = $actions['uses'][0] . '@' . $actions['uses'][1];
                }

                // Add route for each schema…
                $router->addRoute(
                    $method,
                    $schemaName,
                    $actions + ['as' => "graphql.$schemaName"]
                );

                // … and the default schema against the group itself
                if ($schemaName === $defaultSchema) {
                    $router->addRoute(
                        $method,
                        '',
                        $actions + ['as' => 'graphql']
                    );
                }
            }
        }
    );
}
