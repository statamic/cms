<?php

namespace Tests\Feature\Globals;

use Facades\Tests\Factories\GlobalFactory;
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
            ->assertViewHas('values', ['foo' => 'bar', 'unused' => null]);
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

        // GlobalFactory would have created the variables/localization, so we'll remove it for this test.
        $global->in('en')->delete();

        $this
            ->actingAs($user)
            ->get($global->in('en')->editUrl())
            ->assertSuccessful()
            ->assertViewHas('values', ['foo' => null, 'unused' => null]);
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
