<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\FormConnections;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormConnectionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        tap(Form::make('contact')->connections([
            'email' => [['id' => 'abc', 'to' => ['test@example.com']]],
            'webhook' => [['id' => 'def', 'url' => 'https://example.com/webhook']],
        ]))->save();
    }

    #[Test]
    public function it_preloads_connection_types_and_components()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $preload = $this->fieldtype()->preload();

        $this->assertEquals('contact', $preload['form']['handle']);

        $types = collect($preload['types'])->keyBy('handle');

        $this->assertEquals(1, $types['email']['count']);
        $this->assertEquals(1, $types['webhook']['count']);
        $this->assertEquals(cp_route('forms.connect.update', ['contact', 'webhook']), $types['webhook']['action']);

        $this->assertEquals('email-connection', $preload['components']['email']->toArray()['name']);
        $this->assertEquals('webhook-connection', $preload['components']['webhook']->toArray()['name']);
    }

    #[Test]
    public function it_pre_processes_each_connection_type()
    {
        $value = $this->fieldtype()->preProcess([
            'webhook' => [['id' => 'def', 'url' => 'https://example.com/webhook']],
        ]);

        $this->assertEquals('def', $value['webhook'][0]['id']);
        $this->assertTrue($value['webhook'][0]['enabled']);
    }

    #[Test]
    public function it_processes_each_connection_type()
    {
        $value = $this->fieldtype()->process([
            'webhook' => [['url' => 'https://example.com/entry-webhook']],
        ]);

        $this->assertEquals('https://example.com/entry-webhook', $value['webhook'][0]['url']);
    }

    #[Test]
    public function it_drops_unknown_connection_handles()
    {
        $this->assertNull($this->fieldtype()->process(['unknown' => [['foo' => 'bar']]]));
    }

    #[Test]
    public function it_processes_to_null_when_empty()
    {
        $this->assertNull($this->fieldtype()->process([]));
    }

    private function fieldtype(array $config = []): FormConnections
    {
        return (new FormConnections)->setField(
            new Field('connections', array_merge(['type' => 'form_connections', 'form' => 'contact'], $config))
        );
    }
}
