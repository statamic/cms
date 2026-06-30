<?php

namespace Tests\Feature\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Form;
use Statamic\Facades\Nav;
use Statamic\Facades\Role;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Query\Scopes\Scope;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RelationshipFieldtypeTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $collection;

    public function setUp(): void
    {
        parent::setUp();

        $this->collection = Collection::make('test')->save();

        app('statamic.scopes')[StartsWithC::handle()] = StartsWithC::class;

        // Register one scope class under an alias that differs from its handle, so the
        // test can prove the configured alias is what reaches the scope.
        app('statamic.scopes')['fruit_filter'] = AliasedScope::class;
    }

    #[Test]
    public function it_filters_entries_by_query_scopes()
    {
        Entry::make()->collection('test')->slug('apple')->data(['title' => 'Apple'])->save();
        Entry::make()->collection('test')->slug('carrot')->data(['title' => 'Carrot'])->save();
        Entry::make()->collection('test')->slug('cherry')->data(['title' => 'Cherry'])->save();
        Entry::make()->collection('test')->slug('banana')->data(['title' => 'Banana'])->save();

        $this->setTestRoles(['test' => ['access cp', 'view test entries']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'entries',
            'collections' => ['test'],
            'query_scopes' => ['starts_with_c'],
        ]));

        $response = $this
            ->actingAs($user)
            ->get("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk();

        $titles = collect($response->json('data'))->pluck('title')->all();

        $this->assertCount(2, $titles);
        $this->assertContains('Carrot', $titles);
        $this->assertContains('Cherry', $titles);
        $this->assertNotContains('Apple', $titles);
        $this->assertNotContains('Banana', $titles);
    }

    #[Test]
    public function it_passes_the_active_scope_handles_to_aliased_scopes()
    {
        Entry::make()->collection('test')->slug('apple')->data(['title' => 'Apple'])->save();
        Entry::make()->collection('test')->slug('carrot')->data(['title' => 'Carrot'])->save();
        Entry::make()->collection('test')->slug('cherry')->data(['title' => 'Cherry'])->save();
        Entry::make()->collection('test')->slug('banana')->data(['title' => 'Banana'])->save();

        $this->setTestRoles(['test' => ['access cp', 'view test entries']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'entries',
            'collections' => ['test'],
            'query_scopes' => ['fruit_filter'],
        ]));

        $response = $this
            ->actingAs($user)
            ->get("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk();

        $titles = collect($response->json('data'))->pluck('title')->all();

        // The scope only filters when its alias is present in the queryScopes param.
        $this->assertEqualsCanonicalizing(['Carrot', 'Cherry'], $titles);
    }

    #[Test]
    public function it_limits_access_to_entries_from_collections_the_user_can_view()
    {
        Collection::make('pages')->save();
        Entry::make()->collection('pages')->slug('home')->data(['title' => 'Home'])->save();

        Collection::make('secret')->save();
        Entry::make()->collection('secret')->slug('secret-one')->data(['title' => 'Secret One'])->save();

        $this->setTestRoles(['test' => ['access cp', 'view pages entries']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'entries',
            'collections' => ['pages', 'secret'],
        ]));

        $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['slug' => 'home'],
                ],
            ]);
    }

    #[Test]
    public function it_returns_an_empty_listing_when_user_cannot_view_any_of_the_collections()
    {
        Collection::make('pages')->save();
        Entry::make()->collection('pages')->slug('home')->data(['title' => 'Home'])->save();

        Collection::make('secret')->save();
        Entry::make()->collection('secret')->slug('secret-one')->data(['title' => 'Secret One'])->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'entries',
            'collections' => ['pages', 'secret'],
        ]));

        $response = $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk()
            ->assertJsonCount(0, 'data');

        // The columns must not be derived from a blueprint the user can't view.
        $this->assertEmpty($response->json('meta.columns'));
    }

    #[Test]
    public function it_forbids_access_to_entries_when_filters_target_collections_the_user_cannot_view()
    {
        Collection::make('secret')->save();
        Entry::make()->collection('test')->slug('apple')->data(['title' => 'Apple'])->save();
        Entry::make()->collection('secret')->slug('secret-one')->data(['title' => 'Secret One'])->save();

        $this->setTestRoles([
            'test' => ['access cp', 'view test entries'],
        ]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'entries',
            'collections' => ['test'],
        ]));
        $filters = base64_encode(json_encode([
            'collection' => ['collections' => ['secret']],
        ]));

        $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}&filters={$filters}")
            ->assertForbidden();
    }

    #[Test]
    public function it_limits_access_to_terms_from_taxonomies_the_user_can_view()
    {
        Taxonomy::make('topics')->save();
        Taxonomy::make('secret')->save();
        Term::make('public')->taxonomy('topics')->data([])->save();
        Term::make('internal')->taxonomy('secret')->data([])->save();

        $this->setTestRoles(['test' => ['access cp', 'view topics terms']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'terms',
            'taxonomies' => ['topics', 'secret'],
        ]));

        $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['slug' => 'public'],
                ],
            ]);
    }

    #[Test]
    public function it_returns_an_empty_listing_when_the_user_cannot_view_any_of_the_taxonomies()
    {
        Taxonomy::make('topics')->save();
        Taxonomy::make('secret')->save();
        Term::make('public')->taxonomy('topics')->data([])->save();
        Term::make('internal')->taxonomy('secret')->data([])->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'terms',
            'taxonomies' => ['topics', 'secret'],
        ]));

        $response = $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk()
            ->assertJsonCount(0, 'data');

        // The columns must not be derived from a blueprint the user can't view.
        $this->assertEmpty($response->json('meta.columns'));
    }

    #[Test]
    public function it_does_not_expose_columns_from_an_unviewable_taxonomy_blueprint()
    {
        Taxonomy::make('secret')->title('Secret')->save();
        Taxonomy::make('topics')->title('Topics')->save();
        Blueprint::make('secret')
            ->setNamespace('taxonomies.secret')
            ->setContents(['fields' => [
                ['handle' => 'classified', 'field' => ['type' => 'text']],
            ]])
            ->save();
        Blueprint::make('topics')
            ->setNamespace('taxonomies.topics')
            ->setContents(['fields' => [
                ['handle' => 'summary', 'field' => ['type' => 'text']],
            ]])
            ->save();

        // The user can view the second configured taxonomy but not the first.
        $this->setTestRoles(['test' => ['access cp', 'view topics terms']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'terms',
            'taxonomies' => ['secret', 'topics'],
        ]));

        $response = $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk();

        $columns = collect($response->json('meta.columns'))->pluck('field')->all();

        // Columns come from the viewable taxonomy, never the unviewable first one.
        $this->assertNotContains('classified', $columns);
        $this->assertContains('summary', $columns);
    }

    #[Test]
    public function an_authorized_user_still_gets_the_full_taxonomy_columns()
    {
        Taxonomy::make('secret')->title('Secret')->save();
        Blueprint::make('secret')
            ->setNamespace('taxonomies.secret')
            ->setContents(['fields' => [
                ['handle' => 'classified', 'field' => ['type' => 'text']],
            ]])
            ->save();

        $this->setTestRoles(['test' => ['access cp', 'view secret terms']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode([
            'type' => 'terms',
            'taxonomies' => ['secret'],
        ]));

        $response = $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk();

        $columns = collect($response->json('meta.columns'))->pluck('field')->all();

        $this->assertContains('classified', $columns);
    }

    #[Test]
    public function it_scopes_collection_listing_to_viewable_collections()
    {
        Collection::make('pages')->title('Pages')->save();
        Collection::make('secret')->title('Secret')->save();

        $this->setTestRoles(['test' => ['access cp', 'view pages entries']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'collections']));

        $response = $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains('pages', $ids);
        $this->assertNotContains('secret', $ids);
    }

    #[Test]
    public function it_returns_empty_collection_listing_when_no_collections_are_viewable()
    {
        Collection::make('pages')->title('Pages')->save();
        Collection::make('secret')->title('Secret')->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'collections']));

        $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    #[Test]
    public function it_returns_an_empty_user_listing_for_a_user_who_cannot_view_users()
    {
        User::make()->email('one@example.com')->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'users']));

        $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    #[Test]
    public function it_returns_an_empty_user_role_listing_for_a_user_who_cannot_edit_roles()
    {
        Role::make('one')->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'user_roles']));

        $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    #[Test]
    public function it_returns_a_placeholder_for_an_unviewable_item_by_id()
    {
        Collection::make('pages')->title('Pages')->save();
        Collection::make('secret')->title('Secret')->save();

        $this->setTestRoles(['test' => ['access cp', 'view pages entries']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'collections']));

        $response = $this
            ->actingAs($user)
            ->postJson('/cp/fieldtypes/relationship/data', [
                'config' => $config,
                'selections' => ['pages', 'secret'],
            ])
            ->assertOk();

        $data = collect($response->json('data'))->keyBy('id');

        $this->assertEquals('Pages', $data['pages']['title']);
        $this->assertArrayNotHasKey('invalid', $data['pages']);

        $this->assertTrue($data['secret']['invalid']);
        $this->assertEquals('secret', $data['secret']['title']);
    }

    #[Test]
    public function the_by_id_placeholder_does_not_reveal_whether_an_item_exists()
    {
        Collection::make('secret')->title('Secret')->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'collections']));

        $response = $this
            ->actingAs($user)
            ->postJson('/cp/fieldtypes/relationship/data', [
                'config' => $config,
                'selections' => ['secret', 'does-not-exist'],
            ])
            ->assertOk();

        $data = collect($response->json('data'))->keyBy('id');

        $this->assertEquals(
            ['id' => 'secret', 'title' => 'secret', 'invalid' => true],
            $data['secret']
        );
        $this->assertEquals(
            ['id' => 'does-not-exist', 'title' => 'does-not-exist', 'invalid' => true],
            $data['does-not-exist']
        );
    }

    #[Test]
    public function it_returns_a_placeholder_by_id_for_policy_less_types_without_the_permission()
    {
        Role::make('editor')->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'user_roles']));

        $response = $this
            ->actingAs($user)
            ->postJson('/cp/fieldtypes/relationship/data', [
                'config' => $config,
                'selections' => ['editor'],
            ])
            ->assertOk();

        $this->assertTrue($response->json('data.0.invalid'));
        $this->assertEquals('editor', $response->json('data.0.title'));
    }

    #[Test]
    public function a_super_admin_gets_full_data_for_policy_less_types_by_id()
    {
        Role::make('editor')->title('Editor')->save();

        $config = base64_encode(json_encode(['type' => 'user_roles']));

        $response = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->postJson('/cp/fieldtypes/relationship/data', [
                'config' => $config,
                'selections' => ['editor'],
            ])
            ->assertOk();

        $this->assertEquals('Editor', $response->json('data.0.title'));
        $this->assertNull($response->json('data.0.invalid'));
    }

    #[Test]
    public function a_super_admin_can_list_policy_less_types()
    {
        Role::make('editor')->title('Editor')->save();

        $config = base64_encode(json_encode(['type' => 'user_roles']));

        $response = $this
            ->actingAs(User::make()->makeSuper()->save())
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk();

        $this->assertContains('editor', collect($response->json('data'))->pluck('id')->all());
    }

    #[Test]
    public function it_scopes_structure_listing_to_viewable_navs_and_collections()
    {
        Nav::make('main')->title('Main')->save();
        Nav::make('secret')->title('Secret')->save();
        Collection::make('pages')->title('Pages')->structureContents(['root' => true])->save();
        Collection::make('hidden')->title('Hidden')->structureContents(['root' => true])->save();

        $this->setTestRoles(['test' => ['access cp', 'view main nav', 'view pages entries']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'structures']));

        $response = $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains('main', $ids);
        $this->assertContains('collection::pages', $ids);
        $this->assertNotContains('secret', $ids);
        $this->assertNotContains('collection::hidden', $ids);
    }

    #[Test]
    public function it_does_not_leak_structures_across_types()
    {
        Nav::make('main')->title('Main')->save();
        Collection::make('pages')->title('Pages')->structureContents(['root' => true])->save();

        // A user with only a nav permission must not see (or resolve) collection-backed structures.
        $this->setTestRoles(['test' => ['access cp', 'view main nav']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'structures']));

        $listing = $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk();

        $ids = collect($listing->json('data'))->pluck('id')->all();
        $this->assertContains('main', $ids);
        $this->assertNotContains('collection::pages', $ids);

        $byId = $this
            ->actingAs($user)
            ->postJson('/cp/fieldtypes/relationship/data', [
                'config' => $config,
                'selections' => ['main', 'collection::pages'],
            ])
            ->assertOk();

        $data = collect($byId->json('data'))->keyBy('id');
        $this->assertArrayNotHasKey('invalid', $data['main']);
        $this->assertTrue($data['collection::pages']['invalid']);
    }

    #[Test]
    public function it_returns_a_placeholder_for_an_unviewable_structure_by_id()
    {
        Nav::make('secret')->title('Secret')->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'structures']));

        $response = $this
            ->actingAs($user)
            ->postJson('/cp/fieldtypes/relationship/data', [
                'config' => $config,
                'selections' => ['secret'],
            ])
            ->assertOk();

        $this->assertTrue($response->json('data.0.invalid'));
        $this->assertEquals('secret', $response->json('data.0.title'));
    }

    #[Test]
    public function the_structure_by_id_placeholder_does_not_reveal_whether_a_structure_exists()
    {
        Nav::make('secret')->title('Secret')->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'structures']));

        $response = $this
            ->actingAs($user)
            ->postJson('/cp/fieldtypes/relationship/data', [
                'config' => $config,
                'selections' => ['secret', 'does-not-exist'],
            ])
            ->assertOk();

        $data = collect($response->json('data'))->keyBy('id');

        $this->assertEquals(
            ['id' => 'secret', 'title' => 'secret', 'invalid' => true],
            $data['secret']
        );
        $this->assertEquals(
            ['id' => 'does-not-exist', 'title' => 'does-not-exist', 'invalid' => true],
            $data['does-not-exist']
        );
    }

    #[Test]
    public function a_super_admin_sees_all_structures()
    {
        Nav::make('main')->title('Main')->save();
        Collection::make('pages')->title('Pages')->structureContents(['root' => true])->save();

        $config = base64_encode(json_encode(['type' => 'structures']));
        $user = User::make()->makeSuper()->save();

        $listing = $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk();

        $ids = collect($listing->json('data'))->pluck('id')->all();
        $this->assertContains('main', $ids);
        $this->assertContains('collection::pages', $ids);

        $byId = $this
            ->actingAs($user)
            ->postJson('/cp/fieldtypes/relationship/data', [
                'config' => $config,
                'selections' => ['main', 'collection::pages'],
            ])
            ->assertOk();

        $data = collect($byId->json('data'))->keyBy('id');
        $this->assertArrayNotHasKey('invalid', $data['main']);
        $this->assertArrayNotHasKey('invalid', $data['collection::pages']);
    }

    #[Test]
    public function it_scopes_form_listing_to_viewable_forms()
    {
        Form::make('contact')->title('Contact')->save();
        Form::make('secret')->title('Secret')->save();

        $this->setTestRoles(['test' => ['access cp', 'view contact form submissions']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'form']));

        $response = $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains('contact', $ids);
        $this->assertNotContains('secret', $ids);
    }

    #[Test]
    public function it_returns_a_placeholder_for_an_unviewable_form_by_id()
    {
        Form::make('contact')->title('Contact')->save();
        Form::make('secret')->title('Secret')->save();

        $this->setTestRoles(['test' => ['access cp', 'view contact form submissions']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'form']));

        $response = $this
            ->actingAs($user)
            ->postJson('/cp/fieldtypes/relationship/data', [
                'config' => $config,
                'selections' => ['contact', 'secret'],
            ])
            ->assertOk();

        $data = collect($response->json('data'))->keyBy('id');

        $this->assertArrayNotHasKey('invalid', $data['contact']);
        $this->assertTrue($data['secret']['invalid']);
        $this->assertEquals('secret', $data['secret']['title']);
    }

    #[Test]
    public function the_form_by_id_placeholder_does_not_reveal_whether_a_form_exists()
    {
        Form::make('secret')->title('Secret')->save();

        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $config = base64_encode(json_encode(['type' => 'form']));

        $response = $this
            ->actingAs($user)
            ->postJson('/cp/fieldtypes/relationship/data', [
                'config' => $config,
                'selections' => ['secret', 'does-not-exist'],
            ])
            ->assertOk();

        $data = collect($response->json('data'))->keyBy('id');

        $this->assertEquals(
            ['id' => 'secret', 'title' => 'secret', 'invalid' => true],
            $data['secret']
        );
        $this->assertEquals(
            ['id' => 'does-not-exist', 'title' => 'does-not-exist', 'invalid' => true],
            $data['does-not-exist']
        );
    }

    #[Test]
    public function a_super_admin_sees_all_forms()
    {
        Form::make('contact')->title('Contact')->save();
        Form::make('secret')->title('Secret')->save();

        $config = base64_encode(json_encode(['type' => 'form']));
        $user = User::make()->makeSuper()->save();

        $listing = $this
            ->actingAs($user)
            ->getJson("/cp/fieldtypes/relationship?config={$config}")
            ->assertOk();

        $ids = collect($listing->json('data'))->pluck('id')->all();
        $this->assertContains('contact', $ids);
        $this->assertContains('secret', $ids);

        $byId = $this
            ->actingAs($user)
            ->postJson('/cp/fieldtypes/relationship/data', [
                'config' => $config,
                'selections' => ['contact', 'secret'],
            ])
            ->assertOk();

        $data = collect($byId->json('data'))->keyBy('id');
        $this->assertArrayNotHasKey('invalid', $data['contact']);
        $this->assertArrayNotHasKey('invalid', $data['secret']);
    }
}

class StartsWithC extends Scope
{
    public function apply($query, $params)
    {
        $query->where('title', 'like', 'C%');
    }
}

class AliasedScope extends Scope
{
    public function apply($query, $params)
    {
        if (in_array('fruit_filter', $params['queryScopes'] ?? [])) {
            $query->where('title', 'like', 'C%');
        }
    }
}
