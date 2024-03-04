<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

abstract class FieldtypeTestCase extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    protected function createEntryWithFields($arr)
    {
        $blueprint = Blueprint::makeFromFields(
            collect($arr)->map->field->all()
        );

        BlueprintRepository::shouldReceive('in')->with('collections/test')->andReturn(collect([
            'blueprint' => $blueprint->setHandle('blueprint'),
        ]));

        return EntryFactory::collection('test')->id('1')->data(
            collect($arr)->map->value->all()
        )->create();
    }

    protected function assertGqlEntryHas($query, $expected)
    {
        $query = <<<GQL
{
    entry(id: "1") {
        ... on Entry_Test_Blueprint {
            $query
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => $expected,
            ]]);
    }
}
