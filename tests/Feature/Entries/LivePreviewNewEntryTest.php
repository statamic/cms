<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LivePreviewNewEntryTest extends TestCase
{
    use FakesRoles;
    use FakesViews;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->withFakeViews();
        $this->viewShouldReturnRaw('layout', '{{ template_content }}');
        $this->viewShouldReturnRaw('entry', '<h1>{{ title }}</h1> {{ url }}');
    }

    #[Test]
    public function it_previews_a_new_entry_in_a_dated_collection()
    {
        Collection::make('blog')->routes('/blog/{year}/{slug}')->dated(true)->template('entry')->save();

        $url = $this->previewUrl('blog', [
            'title' => 'Dated entry',
            'slug' => 'dated-entry',
            'date' => '2024-03-05T14:30:00.000Z',
        ]);

        $this->assertStringStartsWith('http://localhost/blog/2024/dated-entry?', $url);
        $this->get($url)->assertOk()->assertSee('<h1>Dated entry</h1>', false);
    }

    #[Test]
    public function it_previews_a_new_entry_in_a_dated_collection_without_a_date()
    {
        Collection::make('blog')->routes('/blog/{year}/{slug}')->dated(true)->template('entry')->save();

        $url = $this->previewUrl('blog', ['title' => 'Undated entry', 'slug' => 'undated-entry']);

        $this->assertStringStartsWith('http://localhost/blog/'.now()->year.'/undated-entry?', $url);
        $this->get($url)->assertOk()->assertSee('<h1>Undated entry</h1>', false);
    }

    #[Test]
    public function it_previews_a_new_top_level_entry_in_a_structured_collection()
    {
        $this->structuredCollection();

        $url = $this->previewUrl('pages', ['title' => 'New page', 'slug' => 'new-page']);

        $this->assertStringStartsWith('http://localhost/new-page?', $url);
        $this->get($url)->assertOk()->assertSee('<h1>New page</h1>', false)->assertDontSee('Home');
    }

    #[Test]
    public function it_previews_a_new_entry_under_its_parent_in_a_structured_collection()
    {
        $this->structuredCollection();

        $url = $this->previewUrl('pages', ['title' => 'Child page', 'slug' => 'child-page'], parent: 'about');

        $this->assertStringStartsWith('http://localhost/about/child-page?', $url);
        $this->get($url)->assertOk()->assertSee('<h1>Child page</h1>', false);
    }

    #[Test]
    public function it_treats_the_root_page_as_no_parent()
    {
        $this->structuredCollection();

        $url = $this->previewUrl('pages', ['title' => 'Sibling page', 'slug' => 'sibling-page'], parent: 'home');

        $this->assertStringStartsWith('http://localhost/sibling-page?', $url);
        $this->get($url)->assertOk()->assertSee('<h1>Sibling page</h1>', false);
    }

    private function structuredCollection(): void
    {
        $collection = tap(Collection::make('pages')->routes('{parent_uri}/{slug}')->template('entry')->structureContents(['root' => true]))->save();

        EntryFactory::id('home')->slug('home')->collection('pages')->data(['title' => 'Home'])->create();
        EntryFactory::id('about')->slug('about')->collection('pages')->data(['title' => 'About'])->create();

        $collection->structure()->in('en')->tree([
            ['entry' => 'home'],
            ['entry' => 'about'],
        ])->save();
    }

    private function previewUrl(string $collection, array $values, ?string $parent = null): string
    {
        $uri = "/cp/collections/{$collection}/entries/create/en/preview".($parent ? "?parent={$parent}" : '');

        return $this->actingAs($this->user())->postJson($uri, ['preview' => $values])->assertOk()->json('url');
    }

    private function user()
    {
        $this->setTestRoles(['test' => ['access cp', 'create blog entries', 'create pages entries']]);

        return User::make()->assignRole('test')->save();
    }
}
