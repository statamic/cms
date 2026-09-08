<?php

namespace Tests\Fieldtypes;

use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fields\FieldTransformer;
use Statamic\Fieldtypes\Sets;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SetConfigTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function tearDown(): void
    {
        (new ReflectionProperty(Sets::class, 'setConfigFields'))->setValue(null, []);

        parent::tearDown();
    }

    #[Test]
    public function it_preloads_registered_fields_and_their_processed_defaults()
    {
        Sets::appendSetConfigField('note', ['type' => 'textarea', 'display' => 'Note']);
        Sets::appendSetConfigFields(['enabled' => ['type' => 'toggle', 'default' => true]]);

        $meta = (new Sets)->preload()['setConfig'];

        $this->assertEquals(['note', 'enabled'], array_column($meta['fields'], 'handle'));
        $this->assertSame(true, $meta['defaults']['values']['enabled']);
        $this->assertArrayHasKey('note', $meta['defaults']['meta']);
        $this->assertFalse((new Sets)->configFields()->all()->has('note'));
    }

    #[Test]
    #[DataProvider('fieldtypes')]
    public function it_round_trips_nested_config_through_field_configuration_and_yaml($type)
    {
        $this->registerFields();
        $fieldtype = (new Field('content', ['type' => $type]))->fieldtype();
        $sets = $this->sets();
        $values = $fieldtype->configBlueprint()->fields()->addValues(['sets' => $sets])->preProcess()->values()->all();
        $section = &$values['sets'][0]['sections'][0];

        $this->assertEquals('Original note', $section['extraConfig']['values']['addon']['note']);
        $this->assertArrayHasKey('addon', $section['extraConfig']['meta']);
        $section['handle'] = 'renamed';
        $section['extraConfig']['values']['addon']['note'] = 'Edited note';
        $section['extraConfig']['values']['addon']['enabled'] = false;

        $fields = $fieldtype->configBlueprint()->fields()->addValues($values);
        $fields->validate();
        $processed = $fields->process()->values()->all();
        $yaml = FieldTransformer::fromVue([
            'type' => 'inline',
            'handle' => 'content',
            'fieldtype' => $type,
            'config' => array_merge($processed, ['type' => $type]),
        ]);
        $saved = $yaml['field']['sets']['main']['sets']['renamed'];

        $this->assertEquals(['note' => 'Edited note', 'enabled' => false], $saved['addon']);
        $this->assertEquals(['keep' => 'Unknown addon data'], $saved['other_addon']);
        $this->assertEquals($sets['main']['sets']['hero']['fields'], $saved['fields']);
        $this->assertArrayNotHasKey('extraConfig', $saved);
        $this->assertArrayNotHasKey('_id', $saved);
        $reopened = $fieldtype->configBlueprint()->fields()->addValues($yaml['field'])->preProcess()->values()->get('sets');
        $this->assertEquals('Edited note', $reopened[0]['sections'][0]['extraConfig']['values']['addon']['note']);
    }

    public static function fieldtypes(): array
    {
        return [['replicator'], ['bard']];
    }

    #[Test]
    public function it_preserves_metadata_without_the_registering_addon_and_in_legacy_sets()
    {
        $legacy = $this->sets()['main']['sets'];
        $fieldtype = new Sets;
        $preprocessed = $fieldtype->preProcess($legacy);
        $saved = $fieldtype->process($preprocessed)['main']['sets']['hero'];

        $this->assertEquals($legacy['hero']['addon'], $saved['addon']);
        $this->assertEquals($legacy['hero']['other_addon'], $saved['other_addon']);
    }

    #[Test]
    public function it_keeps_config_with_its_set_when_reordering_or_duplicating()
    {
        $this->registerFields();
        $fieldtype = new Sets;
        $tabs = $fieldtype->preProcess($this->sets());
        $copy = $tabs[0]['sections'][0];
        $copy['handle'] = 'copy';
        $copy['extraConfig']['values']['addon']['note'] = 'Copied note';
        array_unshift($tabs[0]['sections'], $copy);
        $saved = $fieldtype->process($tabs)['main']['sets'];

        $this->assertEquals(['copy', 'hero'], array_keys($saved));
        $this->assertEquals('Copied note', $saved['copy']['addon']['note']);
        $this->assertEquals('Original note', $saved['hero']['addon']['note']);
    }

    #[Test]
    public function it_validates_nested_config_with_the_set_path_and_field_display()
    {
        $this->registerFields();
        $tabs = (new Sets)->preProcess($this->sets());
        $tabs[0]['sections'][0]['extraConfig']['values']['addon']['note'] = '';
        $fields = (new Fields([['handle' => 'sets', 'field' => ['type' => 'sets']]]))->addValues(['sets' => $tabs]);

        try {
            $fields->validate();
            $this->fail('Expected validation to reject the empty note.');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $this->assertArrayHasKey('sets.0.sections.0.extraConfig.values.addon.note', $errors);
            $this->assertStringContainsString('Guidance note', $errors['sets.0.sections.0.extraConfig.values.addon.note'][0]);
        }
    }

    #[Test]
    public function it_does_not_allow_extra_values_to_overwrite_native_set_settings()
    {
        $tabs = (new Sets)->preProcess($this->sets());
        $tabs[0]['sections'][0]['extraConfig']['values']['fields'] = ['overwritten'];
        $tabs[0]['sections'][0]['extraConfig']['values']['display'] = 'Overwritten';
        $tabs[0]['sections'][0]['extraConfig']['values']['_id'] = 'internal';
        $saved = (new Sets)->process($tabs)['main']['sets']['hero'];

        $this->assertEquals('Hero', $saved['display']);
        $this->assertEquals($this->sets()['main']['sets']['hero']['fields'], $saved['fields']);
        $this->assertArrayNotHasKey('_id', $saved);
    }

    #[Test]
    public function it_rejects_registration_of_reserved_handles()
    {
        $this->expectException(\InvalidArgumentException::class);

        Sets::appendSetConfigField('fields', ['type' => 'text']);
    }

    #[Test]
    #[DataProvider('fieldtypes')]
    public function the_field_editor_returns_config_and_rejects_invalid_set_values($type)
    {
        $this->registerFields();
        $user = User::make()->id('editor')->set('super', true);
        $values = $this->actingAs($user)->postJson(cp_route('fields.edit'), [
            'type' => $type,
            'values' => ['handle' => 'content', 'type' => $type, 'sets' => $this->sets()],
        ])->assertOk()->assertJsonPath('meta.sets.setConfig.fields.0.handle', 'addon')->json('values');

        $values['sets'][0]['sections'][0]['extraConfig']['values']['addon']['note'] = '';
        $this->postJson(cp_route('fields.update'), ['type' => $type, 'values' => $values])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sets.0.sections.0.extraConfig.values.addon.note']);

        $values['sets'][0]['sections'][0]['extraConfig']['values']['addon']['note'] = 'Valid note';
        $this->postJson(cp_route('fields.update'), ['type' => $type, 'values' => $values])
            ->assertOk()->assertJsonPath('sets.main.sets.hero.addon.note', 'Valid note');
    }

    private function registerFields(): void
    {
        Sets::appendSetConfigField('addon', [
            'type' => 'group',
            'display' => 'Addon configuration',
            'fields' => [
                ['handle' => 'note', 'field' => ['type' => 'textarea', 'display' => 'Guidance note', 'validate' => 'required']],
                ['handle' => 'enabled', 'field' => ['type' => 'toggle']],
            ],
        ]);
    }

    private function sets(): array
    {
        return ['main' => ['display' => 'Main', 'sets' => ['hero' => [
            'display' => 'Hero',
            'instructions' => 'Native instructions',
            'addon' => ['note' => 'Original note', 'enabled' => true],
            'other_addon' => ['keep' => 'Unknown addon data'],
            'fields' => [['handle' => 'heading', 'field' => ['type' => 'text']]],
        ]]]];
    }
}
