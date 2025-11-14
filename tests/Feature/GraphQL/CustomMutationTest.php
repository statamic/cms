<?php

namespace Tests\Feature\GraphQL;

use GraphQL\Type\Definition\Type;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Rebing\GraphQL\Support\Mutation;
use Statamic\Facades\GraphQL;
use Tests\TestCase;

#[Group('graphql')]
class CustomMutationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        app()->instance('mutation-count', 0);
    }

    #[Test]
    public function custom_mutation_does_not_yet_exist()
    {
        $this
            ->post('/graphql', ['query' => 'mutation { createItem(name: "test") }'])
            ->assertJson(['errors' => [[
                'message' => 'Schema is not configured for mutations.',
            ]]]);
    }

    #[Test]
    #[DefineEnvironment('addCustomMutationsThroughConfig')]
    public function a_custom_mutation_can_be_added_to_the_default_schema_through_config()
    {
        $this
            ->post('/graphql', ['query' => 'mutation { createItem(name: "test") }'])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['createItem' => 'Item created: test']]);
    }

    #[Test]
    #[DefineEnvironment('addCustomMutationsThroughConfig')]
    public function multiple_custom_mutations_can_be_added()
    {
        $this
            ->post('/graphql', ['query' => 'mutation { createItem(name: "first") }'])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['createItem' => 'Item created: first']]);

        $this
            ->post('/graphql', ['query' => 'mutation { updateItem(id: 1, name: "updated") }'])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['updateItem' => 'Item 1 updated: updated']]);
    }

    #[Test]
    #[DefineEnvironment('addCustomMutationsThroughConfig')]
    public function mutations_are_not_cached()
    {
        $this
            ->post('/graphql', ['query' => 'mutation { createItem(name: "test") }'])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['createItem' => 'Item created: test']]);

        $this
            ->post('/graphql', ['query' => 'mutation { createItem(name: "test") }'])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['createItem' => 'Item created: test']]);

        $this->assertEquals(2, app('mutation-count'));
    }

    protected function addCustomMutationsThroughConfig($app)
    {
        $app['config']->set('statamic.graphql.mutations', [
            CreateItemMutation::class,
            UpdateItemMutation::class,
        ]);
    }
}

class CreateItemMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createItem',
    ];

    public function type(): Type
    {
        return GraphQL::string();
    }

    public function args(): array
    {
        return [
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The name of the item to create',
            ],
        ];
    }

    public function resolve($root, $args)
    {
        app()->instance('mutation-count', app('mutation-count') + 1);

        return 'Item created: '.$args['name'];
    }
}

class UpdateItemMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateItem',
    ];

    public function type(): Type
    {
        return GraphQL::string();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'The ID of the item to update',
            ],
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The new name of the item',
            ],
        ];
    }

    public function resolve($root, $args)
    {
        return "Item {$args['id']} updated: {$args['name']}";
    }
}
