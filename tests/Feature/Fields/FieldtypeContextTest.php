<?php

namespace Tests\Feature\Fields;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Fields\Fieldtype;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FieldtypeContextTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        ContextualFieldtype::register();
        UnselectableContextualFieldtype::register();
        $this->actingAs(User::make()->makeSuper()->save());
    }

    #[Test]
    #[DataProvider('pickerContexts')]
    public function picker_visibility_depends_on_context_without_changing_selectability(array $context, bool $forms, bool $visible): void
    {
        $response = $this->getJson('/cp/fields/fieldtypes?'.http_build_query([
            'selectable' => true,
            'forms' => $forms,
            'context' => $context,
        ]))->assertOk();

        $handles = collect($response->json())->pluck('handle');

        $this->assertSame($visible, $handles->contains('contextual'));
        $this->assertNotContains('unselectable_contextual', $handles);
    }

    public static function pickerContexts(): array
    {
        return [
            'top level' => [['blueprint' => 'runway::post', 'nested' => 'false'], false, true],
            'nested' => [['blueprint' => 'runway::post', 'nested' => 'true'], false, false],
            'other owner' => [['blueprint' => 'collections.blog.post', 'nested' => 'false'], false, false],
            'fieldset' => [['fieldset' => 'common'], false, false],
            'no context' => [[], false, false],
            'form' => [['blueprint' => 'runway::post', 'nested' => 'false'], true, false],
        ];
    }

    #[Test]
    public function hidden_fieldtypes_remain_available_outside_the_picker(): void
    {
        $this->getJson('/cp/fields/fieldtypes')->assertOk()
            ->assertJsonFragment(['handle' => 'contextual']);

        $this->postJson('/cp/fields/edit', [
            'type' => 'contextual',
            'values' => ['handle' => 'author'],
            'context' => ['blueprint' => 'collections.blog.post', 'nested' => true],
        ])->assertOk()->assertJsonPath('fieldtype.handle', 'contextual');
    }

    #[Test]
    public function settings_receive_context_separately_from_values_on_edit_and_update(): void
    {
        $context = ['blueprint' => 'runway::post', 'field_path' => ['author'], 'nested' => false];

        $this->postJson('/cp/fields/edit', [
            'type' => 'contextual',
            'context' => $context,
            'values' => ['handle' => 'author'],
        ])->assertOk()->assertJsonPath('values.owner', 'runway::post');

        $this->postJson('/cp/fields/update', [
            'type' => 'contextual',
            'context' => $context,
            'values' => ['handle' => 'author', 'display' => 'Author', 'owner' => 'wrong'],
        ])->assertUnprocessable()->assertJsonValidationErrors('owner');

        $this->postJson('/cp/fields/update', [
            'type' => 'contextual',
            'context' => $context,
            'values' => ['handle' => 'author', 'display' => 'Author', 'owner' => 'runway::post'],
        ])->assertOk()->assertJsonMissingPath('context');
    }

    #[Test]
    public function config_fields_do_not_leak_between_contexts(): void
    {
        $first = new ContextualFieldtype;
        $second = new ContextualFieldtype;

        $this->assertEquals('runway::post', $first->context(['blueprint' => 'runway::post'])->configFields()->get('owner')->defaultValue());
        $this->assertEquals('runway::video', $second->context(['blueprint' => 'runway::video'])->configFields()->get('owner')->defaultValue());
        $this->assertEquals('none', $first->context([])->configFields()->get('owner')->defaultValue());
    }
}

class ContextualFieldtype extends Fieldtype
{
    public function isVisible(): bool
    {
        return ($this->context['blueprint'] ?? null) === 'runway::post'
            && ($this->context['field_path'] ?? null) === []
            && ! ($this->context['nested'] ?? false);
    }

    protected function configFieldItems(): array
    {
        $owner = $this->context['blueprint'] ?? 'none';

        return ['owner' => ['type' => 'text', 'default' => $owner, 'validate' => 'in:'.$owner]];
    }
}

class UnselectableContextualFieldtype extends ContextualFieldtype
{
    protected $selectable = false;
}
