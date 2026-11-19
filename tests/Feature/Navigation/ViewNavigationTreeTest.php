<?php

namespace Tests\Feature\Navigation;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Nav;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewNavigationTreeTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_lists_the_pages_of_the_tree()
    {
        $this->createNavigation();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->getJson(cp_route('navigation.tree.index', ['navigation' => 'main', 'site' => 'en']))
            ->assertOk()
            ->assertExactJson(['pages' => $this->expectedPages(canDelete: true)]);
    }

    #[Test]
    public function it_checks_whether_the_user_can_delete_each_entry()
    {
        $this->createNavigation();

        $this->setTestRoles(['test' => ['access cp', 'view main nav', 'edit main nav', 'view pages entries', 'edit pages entries']]);

        $this
            ->actingAs(tap(User::make()->assignRole('test'))->save())
            ->getJson(cp_route('navigation.tree.index', ['navigation' => 'main', 'site' => 'en']))
            ->assertOk()
            ->assertExactJson(['pages' => $this->expectedPages(canDelete: false)]);
    }

    private function createNavigation()
    {
        $collection = tap(Collection::make('pages')->routes('/{slug}'))->save();
        EntryFactory::id('about')->collection($collection)->slug('about')->data(['title' => 'About'])->create();
        EntryFactory::id('team')->collection($collection)->slug('team')->data(['title' => 'Team', 'redirect' => '/elsewhere'])->published(false)->create();

        $nav = tap(Nav::make('main')->title('Main')->collections(['pages']))->save();

        $nav->makeTree('en', [
            ['id' => 'one', 'entry' => 'about', 'children' => [
                ['id' => 'two', 'entry' => 'team', 'title' => 'Our team'],
            ]],
            ['id' => 'three', 'title' => 'External', 'url' => 'https://example.com'],
            ['id' => 'four', 'title' => 'Just text'],
            ['id' => 'five', 'entry' => 'missing'],
        ])->save();
    }

    private function expectedPages(bool $canDelete)
    {
        $blueprint = ['handle' => 'page', 'title' => 'Page'];

        return [
            [
                'id' => 'one',
                'entry' => 'about',
                'title' => null,
                'entry_title' => 'About',
                'entry_blueprint' => $blueprint,
                'url' => '/about',
                'edit_url' => cp_route('collections.entries.edit', ['pages', 'about']),
                'can_delete' => $canDelete,
                'slug' => 'about',
                'status' => 'published',
                'redirect' => null,
                'collection' => null,
                'children' => [
                    [
                        'id' => 'two',
                        'entry' => 'team',
                        'title' => 'Our team',
                        'entry_title' => 'Team',
                        'entry_blueprint' => $blueprint,
                        'url' => '/elsewhere',
                        'edit_url' => cp_route('collections.entries.edit', ['pages', 'team']),
                        'can_delete' => $canDelete,
                        'slug' => 'team',
                        'status' => 'draft',
                        'redirect' => '/elsewhere',
                        'collection' => null,
                        'children' => [],
                    ],
                ],
            ],
            [
                'id' => 'three',
                'entry' => null,
                'title' => 'External',
                'entry_title' => null,
                'entry_blueprint' => null,
                'url' => 'https://example.com',
                'edit_url' => null,
                'can_delete' => true,
                'slug' => null,
                'status' => null,
                'redirect' => null,
                'collection' => null,
                'children' => [],
            ],
            [
                'id' => 'four',
                'entry' => null,
                'title' => 'Just text',
                'entry_title' => null,
                'entry_blueprint' => null,
                'url' => null,
                'edit_url' => null,
                'can_delete' => true,
                'slug' => null,
                'status' => null,
                'redirect' => null,
                'collection' => null,
                'children' => [],
            ],
        ];
    }
}
