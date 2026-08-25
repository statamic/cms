<?php

namespace Tests\Forms\Connections;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Forms\Connections\Connection;
use Statamic\Support\VueComponent;
use Tests\TestCase;

class ConnectionTest extends TestCase
{
    #[Test]
    public function the_handle_is_snake_cased_from_the_class_by_default()
    {
        $this->assertEquals('test_multi_word', (new TestMultiWordConnection)->handle());
    }

    #[Test]
    public function handle_can_be_defined_as_a_property()
    {
        $connection = new class extends Connection
        {
            protected static $handle = 'example';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals('example', $connection->handle());
    }

    #[Test]
    public function title_is_the_humanized_handle_by_default()
    {
        $this->assertEquals('Test Multi Word', (new TestMultiWordConnection)->title());
    }

    #[Test]
    public function title_can_be_defined_as_a_property()
    {
        $connection = new class extends Connection
        {
            protected static $title = 'Super Cool Example';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals('Super Cool Example', $connection->title());
    }

    #[Test]
    public function it_gets_the_description_and_developer()
    {
        $connection = new class extends Connection
        {
            protected $description = 'Send submissions to Acme.';
            protected $developer = 'Acme Inc';

            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }
        };

        $this->assertEquals('Send submissions to Acme.', $connection->description());
        $this->assertEquals('Acme Inc', $connection->developer());
    }

    #[Test]
    public function it_counts_configured_instances_for_a_form()
    {
        $form = Form::make('contact');

        $connection = new class extends Connection
        {
            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('nothing');
            }

            public function count(\Statamic\Contracts\Forms\Form $form): ?int
            {
                return 3;
            }
        };

        $this->assertEquals(3, $connection->count($form));
    }

    #[Test]
    public function it_is_configured_by_default()
    {
        $this->assertTrue((new TestMultiWordConnection)->isConfigured());
    }

    #[Test]
    public function it_has_no_validation_rules_by_default()
    {
        $this->assertEquals([], (new TestMultiWordConnection)->rules(Form::make('contact')));
    }

    #[Test]
    public function it_processes_the_config_unchanged_by_default()
    {
        $config = [['id' => 'abc', 'foo' => 'bar']];

        $this->assertEquals($config, (new TestMultiWordConnection)->process($config, Form::make('contact')));
    }

    #[Test]
    public function it_pre_processes_the_config_unchanged_by_default()
    {
        $config = [['id' => 'abc', 'foo' => 'bar']];

        $this->assertEquals($config, (new TestMultiWordConnection)->preProcess($config, Form::make('contact')));
    }

    #[Test]
    public function it_renders_a_vue_component()
    {
        $form = Form::make('contact');

        $connection = new class extends Connection
        {
            public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
            {
                return VueComponent::render('acme-connection', [
                    'foo' => 'bar',
                ]);
            }
        };

        $component = $connection->render($form);

        $this->assertInstanceOf(VueComponent::class, $component);
        $this->assertEquals([
            'name' => 'acme-connection',
            'props' => ['foo' => 'bar'],
        ], $component->toArray());
    }
}

class TestMultiWordConnection extends Connection
{
    public function render(\Statamic\Contracts\Forms\Form $form): VueComponent
    {
        return VueComponent::render('nothing');
    }
}
