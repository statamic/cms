<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Taxonomy;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class TaxonomiesFieldtypeTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('default')->andReturn(Blueprint::make());
        Taxonomy::make('tags')->title('Tags')->save();
        Taxonomy::make('colors')->title('Colors')->save();
    }

    #[Test]
    public function it_gets_multiple_taxonomies()
    {
        $article = Blueprint::makeFromFields([
            'related_taxonomies' => ['type' => 'taxonomies'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_taxonomies' => ['tags', 'colors'],
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_taxonomies {
                handle
                title
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'title' => 'Main Post',
                    'related_taxonomies' => [
                        ['handle' => 'tags', 'title' => 'Tags'],
                        ['handle' => 'colors', 'title' => 'Colors'],
                    ],
                ],
            ]]);
    }

    #[Test]
    public function it_gets_single_taxonomy()
    {
        $article = Blueprint::makeFromFields([
            'related_taxonomy' => ['type' => 'taxonomies', 'max_items' => 1],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_taxonomy' => 'tags',
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_taxonomy {
                handle
                title
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'title' => 'Main Post',
                    'related_taxonomy' => [
                        'handle' => 'tags',
                        'title' => 'Tags',
                    ],
                ],
            ]]);
    }
}
