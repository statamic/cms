<?php

namespace Tests\Feature\ResourceIndexes;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use PHPUnit\Framework\Attributes\Test;
use Statamic\CP\ResourceIndex\ResourceIndexRepository;
use Statamic\Facades\Preference;
use Statamic\Facades\ResourceIndex;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ResourceIndexTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private Filesystem $files;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);
        $this->files->delete(resource_path('preferences.yaml'));

        Route::middleware('statamic.cp.authenticated')
            ->get('/cp/resource-index-test', function () {
                $items = [
                    ['id' => 'falcon', 'title' => 'Millennium Falcon', 'organizer_title' => 'Millennium Falcon — Corellia'],
                    ['id' => 'ghost', 'title' => 'Ghost'],
                    ['id' => 'razor-crest', 'title' => 'Razor Crest'],
                ];

                return ResourceIndex::make('ships', $items)
                    ->icon('entries')
                    ->defaultGroups([
                        ['id' => 'rebels', 'title' => 'Rebels', 'items' => ['falcon', 'ghost']],
                        ['id' => 'favorites', 'title' => 'Favorites', 'items' => ['falcon']],
                    ])
                    ->render(fn () => Inertia::render('collections/Index'));
            })
            ->name('resource-index-test');
    }

    public function tearDown(): void
    {
        $this->files->delete(resource_path('preferences.yaml'));

        parent::tearDown();
    }

    #[Test]
    public function a_named_index_passes_through_its_normal_response()
    {
        $this
            ->actingAs($this->user())
            ->get(route('resource-index-test'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('collections/Index')
                ->where('resourceIndex.handle', 'ships')
                ->where('resourceIndex.title', 'Ships')
                ->where('resourceIndex.itemLabel', 'Ship')
                ->where('resourceIndex.organizeUrl', route('resource-index-test').'?resource-index=organize'));
    }

    #[Test]
    public function organization_is_project_wide_and_can_be_reset_to_defaults()
    {
        $user = $this->user();
        $updateUrl = cp_route('resource-indexes.organization.update', 'ships');

        $this
            ->actingAs($user)
            ->get(route('resource-index-test', ['resource-index' => 'organize']))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('resource-indexes/Organize')
                ->where('resourceIndex.handle', 'ships')
                ->where('resourceIndex.itemLabel', 'Ship')
                ->where('resourceIndex.fallbackGroup', [
                    'id' => ResourceIndexRepository::FALLBACK_GROUP,
                    'title' => 'Other',
                ])
                ->where('items.0.title', 'Millennium Falcon — Corellia')
                ->where('items.0.icon', 'entries')
                ->has('groups', 2)
                ->where('hasSavedGroups', false));

        $this
            ->actingAs($user)
            ->patchJson($updateUrl, [
                'groups' => [
                    ['id' => 'custom', 'title' => 'Custom', 'items' => ['razor-crest', 'missing']],
                ],
            ])
            ->assertNoContent();

        $this->assertSame([
            ['id' => 'custom', 'title' => 'Custom', 'items' => ['razor-crest', 'missing']],
        ], Preference::default()->get('resource_indexes.ships.groups'));

        $this
            ->actingAs($user)
            ->deleteJson(cp_route('resource-indexes.organization.destroy', 'ships'))
            ->assertNoContent();

        $this->assertFalse(Preference::default()->hasPreference('resource_indexes.ships.groups'));
    }

    #[Test]
    public function organization_rejects_duplicate_items_and_the_reserved_fallback_id()
    {
        $user = $this->user();

        $this
            ->actingAs($user)
            ->patchJson(cp_route('resource-indexes.organization.update', 'ships'), [
                'groups' => [
                    [
                        'id' => 'duplicates',
                        'title' => 'Duplicates',
                        'items' => ['falcon', 'falcon'],
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['groups.0.items']);

        $this
            ->actingAs($user)
            ->patchJson(cp_route('resource-indexes.organization.update', 'ships'), [
                'groups' => [
                    [
                        'id' => ResourceIndexRepository::FALLBACK_GROUP,
                        'title' => 'Reserved',
                        'items' => [],
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['groups.0.id']);
    }

    #[Test]
    public function organization_requires_the_manage_preferences_permission()
    {
        $this->setTestRoles(['viewer' => ['access cp']]);
        $user = tap(User::make()->assignRole('viewer'))->save();

        $this
            ->actingAs($user)
            ->get(route('resource-index-test', ['resource-index' => 'organize']))
            ->assertForbidden();
    }

    #[Test]
    public function organization_is_available_without_statamic_pro()
    {
        config()->set('statamic.editions.pro', false);

        $user = $this->user();

        $this
            ->actingAs($user)
            ->get(route('resource-index-test'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->where('resourceIndex.organizeUrl', route('resource-index-test').'?resource-index=organize'));

        $this
            ->actingAs($user)
            ->get(route('resource-index-test', ['resource-index' => 'organize']))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page->component('resource-indexes/Organize'));

        $this
            ->actingAs($user)
            ->patchJson(cp_route('resource-indexes.organization.update', 'ships'), [
                'groups' => [
                    ['id' => 'custom', 'title' => 'Custom', 'items' => ['falcon']],
                ],
            ])
            ->assertNoContent();
    }

    private function user()
    {
        return tap(User::make()->makeSuper())->save();
    }
}
