<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class TermsFieldtypeTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    #[Test]
    public function it_gets_multiple_terms()
    {
        $tags = Blueprint::makeFromFields([]);
        $article = Blueprint::makeFromFields([
            'related_terms' => ['type' => 'terms'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'tags' => $tags->setHandle('tags'),
        ]));
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_terms' => ['tags::foo', 'tags::bar'],
        ])->create();

        Taxonomy::make('tags')->save();
        Term::make()->taxonomy('tags')->in('en')->slug('foo')->data(['title' => 'Foo'])->save();
        Term::make()->taxonomy('tags')->in('en')->slug('bar')->data(['title' => 'Bar'])->save();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_terms {
                id
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
                    'related_terms' => [
                        ['id' => 'tags::foo', 'title' => 'Foo'],
                        ['id' => 'tags::bar', 'title' => 'Bar'],
                    ],
                ],
            ]]);
    }

    #[Test]
    public function it_gets_single_entry()
    {
        $tags = Blueprint::makeFromFields([]);
        $article = Blueprint::makeFromFields([
            'related_term' => ['type' => 'terms', 'max_items' => 1],
        ]);

        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([
            'tags' => $tags->setHandle('tags'),
        ]));
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_term' => 'tags::foo',
        ])->create();
        Taxonomy::make('tags')->save();
        Term::make()->taxonomy('tags')->in('en')->slug('foo')->data(['title' => 'Foo'])->save();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_term {
                id
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
                    'related_term' => [
                        'id' => 'tags::foo',
                        'title' => 'Foo',
                    ],
                ],
            ]]);
    }
}
