<?php

namespace Tests\Providers;

use Foo\Bar\TestAddonServiceProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Addons\Addon;
use Statamic\Facades;
use Tests\TestCase;

#[Group('addons')]
class AddonServiceProviderTest extends TestCase
{
    private $addon;
    private $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addon = Addon::makeFromPackage([
            'id' => 'foo/bar',
            'name' => 'The Bar',
            'namespace' => 'Foo\\Bar',
            'provider' => TestAddonServiceProvider::class,
            'autoload' => '',
            'version' => '1.0',
        ]);

        Facades\Addon::shouldReceive('all')->andReturn(collect([$this->addon]));

        $this->provider = new class($this->app) extends TestAddonServiceProvider
        {
            public function callRegisterSettingsBlueprint($blueprint)
            {
                return $this->registerSettingsBlueprint($blueprint);
            }
        };
    }

    #[Test]
    public function it_registers_a_settings_blueprint_from_an_array()
    {
        $this->provider->callRegisterSettingsBlueprint($contents = [
            'tabs' => ['main' => ['sections' => [['fields' => [['handle' => 'api_key', 'field' => ['type' => 'text']]]]]]],
        ]);

        $this->assertTrue($this->addon->hasSettingsBlueprint());
        $this->assertTrue($this->addon->settingsBlueprint()->hasField('api_key'));
        $this->assertEquals($contents, app('statamic.addons.bar.settings_blueprint'));
    }

    #[Test]
    public function it_registers_a_settings_blueprint_from_a_closure_without_evaluating_it()
    {
        $evaluated = 0;

        $this->provider->callRegisterSettingsBlueprint(function () use (&$evaluated) {
            $evaluated++;

            return ['tabs' => ['main' => ['sections' => [['fields' => [['handle' => 'api_key', 'field' => ['type' => 'text']]]]]]]];
        });

        $this->assertTrue($this->addon->hasSettingsBlueprint());
        $this->assertEquals(0, $evaluated);

        $this->assertTrue($this->addon->settingsBlueprint()->hasField('api_key'));
        $this->assertEquals(1, $evaluated);

        $this->addon->settingsBlueprint();
        $this->assertEquals(1, $evaluated);
    }

    #[Test]
    public function it_reevaluates_the_settings_blueprint_closure_in_a_new_scope()
    {
        $evaluated = 0;

        $this->provider->callRegisterSettingsBlueprint(function () use (&$evaluated) {
            $evaluated++;

            return [];
        });

        $this->addon->settingsBlueprint();
        $this->assertEquals(1, $evaluated);

        // Long lived containers (e.g. Octane, queue workers) forget scoped
        // instances between requests, so it shouldn't be cached forever.
        $this->app->forgetScopedInstances();

        $this->addon->settingsBlueprint();
        $this->assertEquals(2, $evaluated);
    }
}
