<?php

namespace Tests\GraphQL;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Assets\Assets;
use Statamic\GraphQL\Types\AssetInterface;
use Statamic\GraphQL\Types\AssetType;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class AssetsFieldtypeGqlTypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_uses_asset_interface_when_improved_types_are_disabled()
    {
        config()->set('statamic.graphql.improved_types.enabled', false);

        GraphQL::shouldReceive('type')
            ->once()
            ->with(AssetInterface::NAME)
            ->andReturn((object) ['name' => AssetInterface::NAME]);

        GraphQL::shouldReceive('addType')->never();
        GraphQL::shouldReceive('listOf')->never();

        $this->fieldtype([
            'container' => 'test_container',
            'max_files' => 1,
        ])->toGqlType();
    }

    #[Test]
    public function it_uses_a_concrete_asset_type_when_a_container_is_configured()
    {
        config()->set('statamic.graphql.improved_types.enabled', true);

        Storage::fake('test', ['url' => '/assets']);
        AssetContainer::make('photos')->disk('test')->save();

        $container = AssetContainer::findByHandle('photos');
        $expected = AssetType::buildName($container);

        GraphQL::shouldReceive('type')
            ->once()
            ->with($expected)
            ->andReturn((object) ['name' => $expected]);

        GraphQL::shouldReceive('addType')->never();
        GraphQL::shouldReceive('listOf')->never();

        $this->fieldtype([
            'container' => 'photos',
            'max_files' => 1,
        ])->toGqlType();
    }

    #[Test]
    public function it_wraps_in_non_null_list_when_max_files_is_not_one()
    {
        config()->set('statamic.graphql.improved_types.enabled', true);

        Storage::fake('test', ['url' => '/assets']);
        AssetContainer::make('documents')->disk('test')->save();

        $container = AssetContainer::findByHandle('documents');
        $expected = AssetType::buildName($container);

        $innerType = (object) ['name' => $expected];

        GraphQL::shouldReceive('type')
            ->once()
            ->with($expected)
            ->andReturn($innerType);

        GraphQL::shouldReceive('nonNull')
            ->once()
            ->with($innerType)
            ->andReturn((object) ['name' => 'NonNull('.$expected.')']);

        GraphQL::shouldReceive('listOf')
            ->once()
            ->andReturn((object) ['name' => 'ListOf(NonNull('.$expected.'))']);

        $this->fieldtype([
            'container' => 'documents',
        ])->toGqlType();
    }

    private function fieldtype(array $config = []): Assets
    {
        $field = new Field('test', array_merge([
            'type' => 'assets',
        ], $config));

        return (new Assets)->setField($field);
    }
}
