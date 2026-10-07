<?php

namespace Tests\OAuth;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Facades\User as UserFacade;
use Statamic\OAuth\Provider;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class OAuthTagsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function defineEnvironment($app)
    {
        $app['config']->set('statamic.oauth.enabled', true);
        $app['config']->set('statamic.oauth.providers', [
            'test' => 'Test',
            'another' => 'Another',
            'stateless' => ['stateless' => true, 'label' => 'Stateless'],
        ]);
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory(storage_path('statamic/oauth'));

        parent::tearDown();
    }

    private function tag($tag, $params = [])
    {
        return Parse::template($tag, $params, trusted: true);
    }

    private function provider(string $name): Provider
    {
        return new Provider($name);
    }

    #[Test]
    public function it_loops_over_providers_excluding_stateless_ones()
    {
        $output = $this->tag('{{ oauth }}{{ name }}:{{ label }};{{ /oauth }}');

        $this->assertEquals('test:Test;another:Another;', $output);
        $this->assertStringNotContainsString('stateless', $output);
    }

    #[Test]
    public function it_outputs_login_urls()
    {
        $output = $this->tag('{{ oauth }}{{ url }};{{ /oauth }}');

        $this->assertEquals(
            route('statamic.oauth.login', 'test').';'.route('statamic.oauth.login', 'another').';',
            $output
        );
    }

    #[Test]
    public function it_appends_a_redirect_to_the_login_urls()
    {
        $output = $this->tag('{{ oauth redirect="/dashboard" }}{{ url }};{{ /oauth }}');

        $this->assertStringContainsString(route('statamic.oauth.login', 'test').'?redirect=/dashboard;', $output);
    }

    #[Test]
    public function the_connected_flag_is_false_for_a_guest()
    {
        $output = $this->tag('{{ oauth }}{{ name }}:{{ if connected }}yes{{ else }}no{{ /if }};{{ /oauth }}');

        $this->assertEquals('test:no;another:no;', $output);
    }

    #[Test]
    public function the_connected_flag_reflects_the_current_users_connections()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();
        $this->provider('test')->setUserProviderId($user, 'sub-1');

        $this->actingAs($user);

        $output = $this->tag('{{ oauth }}{{ name }}:{{ if connected }}yes{{ else }}no{{ /if }};{{ /oauth }}');

        $this->assertEquals('test:yes;another:no;', $output);
    }

    #[Test]
    public function it_outputs_no_results_when_there_are_no_connectable_providers()
    {
        config()->set('statamic.oauth.providers', [
            'stateless' => ['stateless' => true, 'label' => 'Stateless'],
        ]);

        $output = $this->tag('{{ oauth }}{{ name }}{{ if no_results }}none{{ /if }}{{ /oauth }}');

        $this->assertEquals('none', $output);
    }

    #[Test]
    public function the_disconnect_form_renders_a_csrf_protected_delete_form()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();
        $this->actingAs($user);

        $output = $this->tag('{{ oauth:disconnect_form provider="test" }}<button>Disconnect</button>{{ /oauth:disconnect_form }}');

        $this->assertStringContainsString('<form method="POST" action="'.route('statamic.oauth.disconnect', 'test').'"', $output);
        $this->assertStringContainsString('name="_token"', $output);
        $this->assertStringContainsString('name="_method" value="DELETE"', $output);
        $this->assertStringContainsString('<button>Disconnect</button>', $output);
    }

    #[Test]
    public function the_disconnect_form_is_empty_for_a_guest()
    {
        $output = $this->tag('{{ oauth:disconnect_form provider="test" }}<button>Disconnect</button>{{ /oauth:disconnect_form }}');

        $this->assertEquals('', $output);
    }

    #[Test]
    public function the_disconnect_form_is_empty_without_a_provider()
    {
        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();
        $this->actingAs($user);

        $output = $this->tag('{{ oauth:disconnect_form }}<button>Disconnect</button>{{ /oauth:disconnect_form }}');

        $this->assertEquals('', $output);
    }

    #[Test]
    public function the_wildcard_still_outputs_a_login_url()
    {
        $this->assertEquals(route('statamic.oauth.login', 'test'), $this->tag('{{ oauth:test }}'));
    }
}
