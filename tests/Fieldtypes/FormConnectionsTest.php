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
    public function it_preloads_connection_types()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $preload = $this->fieldtype()->preload();

        $this->assertEquals('contact', $preload['form']['handle']);
        $this->assertEquals(['form', 'types'], array_keys($preload));

        $types = collect($preload['types'])->keyBy('handle');

        $this->assertEquals(['email', 'webhook'], $types->keys()->all());
        $this->assertEquals(cp_route('forms.connect.validate', ['contact', 'webhook']), $types['webhook']['action']);
        $this->assertEquals(cp_route('forms.connect.editor', ['contact', 'webhook']), $types['webhook']['editor']);
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
        $this->assertSame([], $this->fieldtype()->process(['unknown' => [['foo' => 'bar']]]));
    }

    #[Test]
    public function it_processes_to_an_empty_array_when_empty()
    {
        $this->assertSame([], $this->fieldtype()->process([]));
    }

    #[Test]
    public function it_handles_a_form_that_does_not_exist()
    {
        $fieldtype = $this->fieldtype(['form' => 'missing']);

        $this->assertEquals(['form' => null, 'types' => []], $fieldtype->preload());
        $this->assertEquals([], $fieldtype->preProcess(['webhook' => [['id' => 'def']]]));
        $this->assertNull($fieldtype->process(['webhook' => [['url' => 'https://example.com/webhook']]]));
    }

    private function fieldtype(array $config = []): FormConnections
    {
        return (new FormConnections)->setField(
            new Field('connections', array_merge(['type' => 'form_connections', 'form' => 'contact'], $config))
        );
    }
}
