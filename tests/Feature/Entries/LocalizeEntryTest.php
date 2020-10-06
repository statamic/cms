<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LocalizeEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        // When using the array driver, the same entry object is always returned which breaks
        // subsequent localization lookups.
        config(['cache.default' => 'file']);
        \Illuminate\Support\Facades\Cache::clear();

        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]]);
    }

    /** @test */
    public function it_localizes_an_entry()
    {
        $user = $this->user();
        $entry = EntryFactory::collection('blog')->slug('test')->create();
        $this->assertNull($entry->in('fr'));

        $response = $this
            ->actingAs($user)
            ->localize($entry, ['site' => 'fr'])
            ->assertOk();

        $localized = $entry->fresh()->in('fr');
        $this->assertNotNull($localized);
        $this->assertEquals($user, $localized->lastModifiedBy());
        $response->assertJson(['handle' => 'fr', 'url' => $localized->editUrl()]);
    }

    /** @test */
    public function site_is_required()
    {
        $entry = EntryFactory::collection('blog')->slug('test')->create();

        $this
            ->actingAs($this->user())
            ->from('/original')
            ->localize($entry, ['site' => ''])
            ->assertRedirect('/original')
            ->assertSessionHasErrors('site');
    }

    private function localize($entry, $params = [])
    {
        $url = cp_route('collections.entries.localize', [
            'collection' => $entry->collectionHandle(),
            'entry' => $entry->id(),
            'slug' => $entry->slug(),
        ]);

        return $this->post($url, $params);
    }

    private function user()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return User::make()->assignRole('test')->save();
    }
}
