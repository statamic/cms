<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Statamic\Fields\FieldtypeRepository;
use Facades\Tests\Factories\EntryFactory;
use Facades\Tests\Factories\GlobalFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Fields\Fieldtype;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class ResolvesValuesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $blueprint;

    public function setUp(): void
    {
        parent::setUp();

        $fieldtype = new class extends Fieldtype
        {
            public function augment($value)
            {
                return strtoupper($value);
            }
        };

        $mock = FieldtypeRepository::partialMock();
        $mock->shouldReceive('find')->with('example')->andReturn($fieldtype);

        $this->blueprint = Blueprint::makeFromFields(['foo' => ['type' => 'example']]);
    }

    #[Test]
    public function it_resolves_values_in_entries()
    {
        BlueprintRepository::shouldReceive('in')->with('collections/test')->andReturn(collect([
            'test' => $this->blueprint->setHandle('test'),
        ]));

        $entry = EntryFactory::id('test')->slug('test')->collection('test')->data(['foo' => 'bar'])->create();

        $this->assertEquals('bar', $entry->value('foo'));
        $this->assertEquals('BAR', $entry->resolveGqlValue('foo'));
        $this->assertEquals('bar', $entry->resolveRawGqlValue('foo'));
    }

    #[Test]
    public function it_resolves_values_in_terms()
    {
        BlueprintRepository::shouldReceive('in')->with('taxonomies/test')->andReturn(collect([
            'test' => $this->blueprint->setHandle('test'),
        ]));

        Taxonomy::make('test')->save();

        $term = tap(Term::make('test')->taxonomy('test')->data(['foo' => 'bar']))->save();
        $term = $term->in('en');

        $this->assertEquals('bar', $term->value('foo'));
        $this->assertEquals('BAR', $term->resolveGqlValue('foo'));
        $this->assertEquals('bar', $term->resolveRawGqlValue('foo'));
    }

    #[Test]
    public function it_resolves_values_in_users()
    {
        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($this->blueprint);

        $user = User::make()->data(['foo' => 'bar']);

        $this->assertEquals('bar', $user->value('foo'));
        $this->assertEquals('BAR', $user->resolveGqlValue('foo'));
        $this->assertEquals('bar', $user->resolveRawGqlValue('foo'));
    }

    #[Test]
    public function it_resolves_values_in_globals()
    {
        BlueprintRepository::shouldReceive('find')->with('globals.test')->andReturn($this->blueprint);

        $set = GlobalFactory::handle('test')->data(['foo' => 'bar'])->create();
        $vars = $set->in('en');

        $this->assertEquals('bar', $vars->value('foo'));
        $this->assertEquals('BAR', $vars->resolveGqlValue('foo'));
        $this->assertEquals('bar', $vars->resolveRawGqlValue('foo'));
    }
}
