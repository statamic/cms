<?php

namespace Tests\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Terms;
use Statamic\GraphQL\Types\DynamicTermUnionType;
use Statamic\GraphQL\Types\TermInterface;
use Statamic\GraphQL\Types\TermType;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class TermsFieldtypeGqlTypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_uses_term_interface_when_no_taxonomies_are_configured()
    {
        GraphQL::shouldReceive('type')
            ->once()
            ->with(TermInterface::NAME)
            ->andReturn((object) ['name' => TermInterface::NAME]);

        GraphQL::shouldReceive('addType')->never();
        GraphQL::shouldReceive('listOf')->never();

        $this->fieldtype([
            // no taxonomies configured
            'max_items' => 1,
        ])->toGqlType();
    }

    #[Test]
    public function it_uses_a_concrete_term_type_when_a_single_blueprint_is_possible()
    {
        /** @var \Statamic\Taxonomies\Taxonomy $taxonomy */
        $taxonomy = tap(Taxonomy::make('tags'))->save();

        /** @var \Statamic\Fields\Blueprint $tag */
        $tag = tap($this->partialMock(\Statamic\Fields\Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('tag');
        });

        BlueprintRepository::shouldReceive('in')
            ->with('taxonomies/tags')
            ->andReturn(collect(['tag' => $tag]));

        TermInterface::addTypes();

        $expected = TermType::buildName($taxonomy, $tag);

        $type = $this->fieldtype([
            'taxonomies' => ['tags'],
            'max_items' => 1,
        ])->toGqlType();

        $this->assertEquals($expected, $type->name);
    }

    #[Test]
    public function it_uses_a_dynamic_union_when_multiple_blueprints_are_possible()
    {
        /** @var \Statamic\Taxonomies\Taxonomy $taxonomy */
        $taxonomy = tap(Taxonomy::make('tags'))->save();

        $primary = tap($this->partialMock(\Statamic\Fields\Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('primary');
        });
        $secondary = tap($this->partialMock(\Statamic\Fields\Blueprint::class), function ($m) {
            $m->shouldReceive('handle')->andReturn('secondary');
        });

        BlueprintRepository::shouldReceive('in')
            ->with('taxonomies/tags')
            ->andReturn(collect(['primary' => $primary, 'secondary' => $secondary]));

        $expectedName = DynamicTermUnionType::getTypeName([
            ['taxonomy' => $taxonomy, 'blueprint' => $primary],
            ['taxonomy' => $taxonomy, 'blueprint' => $secondary],
        ]);

        // Ensure the concrete term types exist before the union resolves its members.
        TermInterface::addTypes();

        /** @var object $type */
        $type = $this->fieldtype([
            'taxonomies' => ['tags'],
            'max_items' => 1,
        ])->toGqlType();

        $this->assertEquals($expectedName, $type->name);
    }

    private function fieldtype(array $config = []): Terms
    {
        $field = new Field('test', array_merge([
            'type' => 'terms',
        ], $config));

        return (new Terms)->setField($field);
    }
}
