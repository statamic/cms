<?php

namespace Tests\Feature\Globals;

use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditGlobalVariablesTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();
        $global = GlobalSet::make('test')->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get($global->in('en')->editUrl())
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_shows_the_form()
    {
        $blueprint = Blueprint::make()->setContents(['fields' => [
            ['handle' => 'foo', 'field' => ['type' => 'text']],
            ['handle' => 'unused', 'field' => ['type' => 'text']],
        ]]);
        Blueprint::partialMock();
        Blueprint::shouldReceive('find')->with('globals.test')->andReturn($blueprint);
        $this->setTestRoles(['test' => ['access cp', 'edit test globals']]);
        $user = User::make()->assignRole('test')->save();

        $global = GlobalSet::make('test')->save();
        $global->in('en')->data(['foo' => 'bar'])->save();

        $this
            ->actingAs($user)
            ->get($global->in('en')->editUrl())
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('globals/Edit')
                ->where('asConfig', false)
                ->has('values', fn (Assert $page) => $page
                    ->where('foo', 'bar')
                    ->where('unused', null)
                )
            );
    }

    #[Test]
    public function it_passes_as_config_when_the_global_set_uses_multi_column_layout()
    {
        $blueprint = Blueprint::make()->setContents(['fields' => [
            ['handle' => 'foo', 'field' => ['type' => 'text']],
        ]]);
        Blueprint::partialMock();
        Blueprint::shouldReceive('find')->with('globals.test')->andReturn($blueprint);
        $this->setTestRoles(['test' => ['access cp', 'edit test globals']]);
        $user = User::make()->assignRole('test')->save();

        $global = GlobalSet::make('test')->layoutMode('multi_column')->save();
        $global->in('en')->data(['foo' => 'bar'])->save();

        $this
            ->actingAs($user)
            ->get($global->in('en')->editUrl())
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('globals/Edit')
                ->where('asConfig', true)
            );
    }

    #[Test]
    public function it_shows_the_form_even_if_localization_does_not_exist()
    {
        $blueprint = Blueprint::make()->setContents(['fields' => [
            ['handle' => 'foo', 'field' => ['type' => 'text']],
            ['handle' => 'unused', 'field' => ['type' => 'text']],
        ]]);
        Blueprint::partialMock();
        Blueprint::shouldReceive('find')->with('globals.test')->andReturn($blueprint);
        $this->setTestRoles(['test' => ['access cp', 'edit test globals']]);
        $user = User::make()->assignRole('test')->save();

        $global = GlobalSet::make('test')->save();

        $this
            ->actingAs($user)
            ->get($global->in('en')->editUrl())
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('globals/Edit')
                ->has('values', fn (Assert $page) => $page
                    ->where('foo', null)
                    ->where('unused', null)
                )
            );
    }

    #[Test]
    public function it_marks_localizations_as_fully_synced_when_they_have_no_localized_data()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
        ]);

        $blueprint = Blueprint::make()->setContents(['fields' => [
            ['handle' => 'foo', 'field' => ['type' => 'text']],
        ]]);
        Blueprint::partialMock();
        Blueprint::shouldReceive('find')->with('globals.test')->andReturn($blueprint);

        $this->setTestRoles(['test' => [
            'access cp',
            'edit test globals',
            'access en site',
            'access fr site',
        ]]);
        $user = User::make()->assignRole('test')->save();

        $global = GlobalSet::make('test')->sites(['en', 'fr' => 'en'])->save();
        $global->in('en')->data(['foo' => 'bar'])->save();
        $global->in('fr')->data(['foo' => 'baz'])->save();

        $this
            ->actingAs($user)
            ->get($global->in('en')->editUrl())
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('globals/Edit')
                ->where('localizations.0.handle', 'en')
                ->where('localizations.0.active', true)
                ->where('localizations.0.root', true)
                ->where('localizations.0.origin', false)
                ->where('localizations.0.fully_synced', false)
                ->where('localizations.1.handle', 'fr')
                ->where('localizations.1.active', false)
                ->where('localizations.1.root', false)
                ->where('localizations.1.origin', false)
                ->where('localizations.1.fully_synced', false)
            );

        $this
            ->actingAs($user)
            ->get($global->in('fr')->editUrl())
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('globals/Edit')
                ->where('localizations.0.handle', 'en')
                ->where('localizations.0.active', false)
                ->where('localizations.0.root', true)
                ->where('localizations.0.origin', true)
                ->where('localizations.1.handle', 'fr')
                ->where('localizations.1.active', true)
                ->where('localizations.1.root', false)
                ->where('localizations.1.origin', false)
                ->where('localizations.1.fully_synced', false)
            );

        $global->in('fr')->data([])->save();

        $this
            ->actingAs($user)
            ->get($global->in('en')->editUrl())
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('globals/Edit')
                ->where('localizations.0.handle', 'en')
                ->where('localizations.0.fully_synced', false)
                ->where('localizations.1.handle', 'fr')
                ->where('localizations.1.fully_synced', true)
            );

        // Matching origin values still counts as unsynced once data is stored locally.
        $global->in('fr')->data(['foo' => 'bar'])->save();

        $this
            ->actingAs($user)
            ->get($global->in('en')->editUrl())
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('globals/Edit')
                ->where('localizations.1.handle', 'fr')
                ->where('localizations.1.fully_synced', false)
            );
    }

    #[Test]
    public function it_marks_root_when_the_origin_is_not_the_root()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://de.test.com/'],
        ]);

        $blueprint = Blueprint::make()->setContents(['fields' => [
            ['handle' => 'foo', 'field' => ['type' => 'text']],
        ]]);
        Blueprint::partialMock();
        Blueprint::shouldReceive('find')->with('globals.test')->andReturn($blueprint);

        $this->setTestRoles(['test' => [
            'access cp',
            'edit test globals',
            'access en site',
            'access fr site',
            'access de site',
        ]]);
        $user = User::make()->assignRole('test')->save();

        $global = GlobalSet::make('test')->sites(['en', 'fr' => 'en', 'de' => 'fr'])->save();
        $global->in('en')->data(['foo' => 'bar'])->save();
        $global->in('fr')->data([])->save();
        $global->in('de')->data([])->save();

        $this
            ->actingAs($user)
            ->get($global->in('de')->editUrl())
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('globals/Edit')
                ->where('localizations.0.handle', 'en')
                ->where('localizations.0.root', true)
                ->where('localizations.0.origin', false)
                ->where('localizations.0.active', false)
                ->where('localizations.1.handle', 'fr')
                ->where('localizations.1.root', false)
                ->where('localizations.1.origin', true)
                ->where('localizations.1.active', false)
                ->where('localizations.2.handle', 'de')
                ->where('localizations.2.root', false)
                ->where('localizations.2.origin', false)
                ->where('localizations.2.active', true)
            );
    }

    #[Test]
    public function it_orders_localizations_by_configured_site_order()
    {
        $this->setSites([
            'fr' => ['name' => 'French', 'locale' => 'fr_FR', 'url' => 'http://fr.test.com/', 'group' => 'EU', 'group_handle' => 'eu'],
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/', 'group' => 'UK', 'group_handle' => 'uk'],
        ]);

        $blueprint = Blueprint::make()->setContents(['fields' => [
            ['handle' => 'foo', 'field' => ['type' => 'text']],
        ]]);
        Blueprint::partialMock();
        Blueprint::shouldReceive('find')->with('globals.test')->andReturn($blueprint);

        $this->setTestRoles(['test' => [
            'access cp',
            'edit test globals',
            'access en site',
            'access fr site',
        ]]);
        $user = User::make()->assignRole('test')->save();

        // Global set sites() keys start with en, but Site::all() starts with fr.
        $global = GlobalSet::make('test')->sites(['en', 'fr' => 'en'])->save();
        $global->in('en')->data(['foo' => 'bar'])->save();
        $global->in('fr')->data([])->save();

        $this
            ->actingAs($user)
            ->get($global->in('en')->editUrl())
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('globals/Edit')
                ->where('localizations.0.handle', 'fr')
                ->where('localizations.0.group', 'EU')
                ->where('localizations.1.handle', 'en')
                ->where('localizations.1.group', 'UK')
            );
    }

    #[Test]
    public function it_404s_if_invalid_site()
    {
        $this->setSites([
            'en' => ['locale' => 'en', 'url' => '/'],
            'fr' => ['locale' => 'fr', 'url' => '/fr/'],
        ]);
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();
        $global = GlobalSet::make('test')->sites(['en', 'fr'])->save();

        $url = $global->in('fr')->editUrl();
        $global->sites(['en'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get($url)
            ->assertNotFound();
    }
}
