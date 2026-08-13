<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class TaxonomyStructureTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['taxonomies', 'terms'];

    public function setUp(): void
    {
        parent::setUp();

        tap(Taxonomy::make('categories')->title('Categories')->structureContents(['max_depth' => 3]))->save();

        foreach (['animals', 'cat', 'furniture'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        Taxonomy::findByHandle('categories')->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'furniture'],
        ])->save();

        ResourceAuthorizer::shouldReceive('isAllowed')->andReturnTrue();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->andReturn(['categories', 'tags']);
        ResourceAuthorizer::makePartial();
    }

    #[Test]
    public function it_queries_the_taxonomy_structure_tree()
    {
        $query = <<<'GQL'
{
    taxonomy(handle: "categories") {
        structure {
            handle
            max_depth
            expects_root
            tree {
                depth
                term {
                    id
                }
                children {
                    depth
                    term {
                        id
                    }
                }
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
                'taxonomy' => [
                    'structure' => [
                        'handle' => 'categories',
                        'max_depth' => 3,
                        'expects_root' => false,
                        'tree' => [
                            [
                                'depth' => 1,
                                'term' => ['id' => 'categories::animals'],
                                'children' => [
                                    [
                                        'depth' => 2,
                                        'term' => ['id' => 'categories::cat'],
                                    ],
                                ],
                            ],
                            [
                                'depth' => 1,
                                'term' => ['id' => 'categories::furniture'],
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ]]);
    }

    #[Test]
    public function structure_is_null_on_flat_taxonomies()
    {
        tap(Taxonomy::make('tags')->title('Tags'))->save();

        // Flat taxonomies don't error; the structure field is nullable.
        $flatQuery = <<<'GQL'
{
    taxonomy(handle: "tags") {
        structure {
            handle
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $flatQuery])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'taxonomy' => [
                    'structure' => null,
                ],
            ]]);
    }

    #[Test]
    public function it_queries_hierarchy_fields_on_terms()
    {
        $query = <<<'GQL'
{
    term(id: "categories::cat") {
        id
        depth
        is_root
        parent {
            id
        }
        ancestors {
            id
        }
        children {
            id
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'term' => [
                    'id' => 'categories::cat',
                    'depth' => 2,
                    'is_root' => false,
                    'parent' => ['id' => 'categories::animals'],
                    'ancestors' => [['id' => 'categories::animals']],
                    'children' => [],
                ],
            ]]);
    }
}
