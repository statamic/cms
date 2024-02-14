<?php

declare(strict_types=1);

namespace Rebing\GraphQL;

use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Rebing\GraphQL\Console\EnumMakeCommand;
use Rebing\GraphQL\Console\FieldMakeCommand;
use Rebing\GraphQL\Console\InputMakeCommand;
use Rebing\GraphQL\Console\InterfaceMakeCommand;
use Rebing\GraphQL\Console\MiddlewareMakeCommand;
use Rebing\GraphQL\Console\MutationMakeCommand;
use Rebing\GraphQL\Console\QueryMakeCommand;
use Rebing\GraphQL\Console\ScalarMakeCommand;
use Rebing\GraphQL\Console\SchemaConfigMakeCommand;
use Rebing\GraphQL\Console\TypeMakeCommand;
use Rebing\GraphQL\Console\UnionMakeCommand;

class GraphQLServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bootPublishes();

        $this->bootRouter();
    }

    /**
     * Bootstrap router.
     */
    protected function bootRouter(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    /**
     * Bootstrap publishes.
     */
    protected function bootPublishes(): void
    {
        $configPath = __DIR__.'/../config';

        $this->mergeConfigFrom($configPath.'/config.php', 'graphql');

        $this->publishes([
            $configPath.'/config.php' => $this->app->configPath().'/graphql.php',
        ], 'config');
    }

    /**
     * Add types from config.
     */
    protected function bootTypes(GraphQL $graphQL): void
    {
        $configTypes = $graphQL->getConfigRepository()->get('graphql.types', []);
        $graphQL->addTypes($configTypes);
    }

    /**
     * Configure security from config.
     */
    protected function applySecurityRules(Repository $config): void
    {
        $maxQueryComplexity = $config->get('graphql.security.query_max_complexity');

        if ($maxQueryComplexity !== null) {
            /** @var QueryComplexity $queryComplexity */
            $queryComplexity = DocumentValidator::getRule(QueryComplexity::class);
            $queryComplexity->setMaxQueryComplexity($maxQueryComplexity);
        }

        $maxQueryDepth = $config->get('graphql.security.query_max_depth');

        if ($maxQueryDepth !== null) {
            /** @var QueryDepth $queryDepth */
            $queryDepth = DocumentValidator::getRule(QueryDepth::class);
            $queryDepth->setMaxQueryDepth($maxQueryDepth);
        }

        $disableIntrospection = $config->get('graphql.security.disable_introspection');

        if ($disableIntrospection === true) {
            /** @var DisableIntrospection $disableIntrospection */
            $disableIntrospection = DocumentValidator::getRule(DisableIntrospection::class);
            $disableIntrospection->setEnabled(DisableIntrospection::ENABLED);
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerGraphQL();

        if ($this->app->runningInConsole()) {
            $this->registerConsole();
        }
    }

    public function registerGraphQL(): void
    {
        $this->app->singleton(GraphQL::class, function (Container $app): GraphQL {
            $config = $app->make(Repository::class);

            $graphql = new GraphQL($app, $config);

            $this->applySecurityRules($config);

            return $graphql;
        });
        $this->app->alias(GraphQL::class, 'graphql');

        $this->app->afterResolving(GraphQL::class, function (GraphQL $graphQL): void {
            $this->bootTypes($graphQL);
        });
    }

    /**
     * Register console commands.
     */
    public function registerConsole(): void
    {
        $this->commands([
            EnumMakeCommand::class,
            FieldMakeCommand::class,
            InputMakeCommand::class,
            InterfaceMakeCommand::class,
            InterfaceMakeCommand::class,
            MiddlewareMakeCommand::class,
            MutationMakeCommand::class,
            QueryMakeCommand::class,
            ScalarMakeCommand::class,
            SchemaConfigMakeCommand::class,
            TypeMakeCommand::class,
            UnionMakeCommand::class,
        ]);
    }
}
