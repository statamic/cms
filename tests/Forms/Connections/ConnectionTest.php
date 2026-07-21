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
    public function the_title_falls_back_to_a_titleized_handle()
    {
        $connection = (new Connection)->handle('zapier');

        $this->assertEquals('Zapier', $connection->title());

        $connection->title('Zapier Workflows');

        $this->assertEquals('Zapier Workflows', $connection->title());
    }

    #[Test]
    public function it_gets_and_sets_the_developer()
    {
        $connection = (new Connection)->handle('zapier');

        $this->assertNull($connection->developer());

        $connection->developer('Zapier Inc');

        $this->assertEquals('Zapier Inc', $connection->developer());
    }

    #[Test]
    public function it_evaluates_component_data_for_a_form()
    {
        $form = Form::make('contact');

        $connection = (new Connection)
            ->handle('zapier')
            ->component('zapier-connection', fn ($form) => ['form' => $form->handle()]);

        $this->assertEquals('zapier-connection', $connection->component());
        $this->assertEquals(['form' => 'contact'], $connection->componentData($form));
    }

    #[Test]
    public function component_data_is_empty_without_a_callback()
    {
        $connection = (new Connection)->handle('zapier')->component('zapier-connection');

        $this->assertEquals([], $connection->componentData(Form::make('contact')));
    }

    #[Test]
    public function it_renders_a_vue_component()
    {
        $form = Form::make('contact');

        $connection = (new Connection)
            ->handle('zapier')
            ->component('zapier-connection', fn ($form) => ['form' => $form->handle()]);

        $component = $connection->render($form);

        $this->assertInstanceOf(VueComponent::class, $component);
        $this->assertEquals([
            'name' => 'zapier-connection',
            'props' => ['form' => 'contact'],
        ], $component->toArray());
    }
}
