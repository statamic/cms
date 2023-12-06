<?php

namespace Tests\Feature\Globals;

use Facades\Tests\Factories\GlobalFactory;
use Illuminate\Support\Facades\Event;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\GlobalVariablesSaved;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateGlobalVariablesTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_denies_access_if_you_dont_have_edit_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'access en site']]);
        $user = User::make()->assignRole('test')->save();
        $global = GlobalFactory::handle('test')->create();

        $this
            ->from('/original')
            ->actingAs($user)
            ->patch($global->in('en')->updateUrl())
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    public function it_denies_access_if_you_dont_have_site_permission()
    {
        Site::setConfig(['sites' => [
            'en' => ['locale' => 'en', 'url' => '/'],
            'fr' => ['locale' => 'fr', 'url' => '/fr/'],
        ]]);
        $this->setTestRoles(['test' => ['access cp', 'edit test globals']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $global = GlobalFactory::handle('test')->data(['foo' => 'bar'])->make();
        $global->addLocalization($global->makeLocalization('fr'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->patch($global->in('fr')->updateUrl(), ['foo' => 'baz'])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    public function global_variables_get_updated()
    {
        $blueprint = Blueprint::make()->setContents(['fields' => [
            ['handle' => 'foo', 'field' => ['type' => 'text']],
        ]]);
        Blueprint::partialMock();
        Blueprint::shouldReceive('find')->with('globals.test')->andReturn($blueprint);
        $this->setTestRoles(['test' => ['access cp', 'edit test globals']]);
        $user = tap(User::make()->assignRole('test')->makeSuper())->save();
        $global = GlobalFactory::handle('test')->data(['foo' => 'bar'])->create();

        Event::fake(); // Fake after initial global has been created so its event isn't tracked.

        $this->assertCount(1, GlobalSet::all());

        $this
            ->from('/here')
            ->actingAs($user)
            ->patchJson($global->in('en')->updateUrl(), ['foo' => 'baz'])
            ->assertSuccessful();

        $this->assertCount(1, GlobalSet::all());
        $global = GlobalSet::find('test')->in('en');
        $this->assertEquals('baz', $global->foo);

        Event::assertDispatched(GlobalVariablesSaved::class, function ($event) {
            return $event->variables->handle() === 'test'
                && $event->variables->data()->all() === ['foo' => 'baz'];
        });

        Event::assertDispatched(GlobalSetSaved::class, function ($event) {
            return $event->globals->handle() === 'test'
                && $event->globals->in('en')->data()->all() === ['foo' => 'baz'];
        });
    }
}
