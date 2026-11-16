<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\FieldtypeInspector;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class FieldtypesShowTest extends TestCase
{
    #[Test]
    public function it_shows_native_options_defaults_and_shared_fields()
    {
        $data = $this->commandJson(['handle' => 'grid']);

        $this->assertSame('3.0.0-testing', $data['statamic_version']);
        $this->assertSame('grid', $data['fieldtype']['handle']);
        $this->assertSame('Statamic\\Fieldtypes\\Grid', $data['fieldtype']['class']);
        $this->assertSame('table', $data['config']['mode']['default']);
        $this->assertSame(['table' => 'Table', 'stacked' => 'Stacked'], $data['config']['mode']['options']);
        $this->assertSame('select', $data['config']['mode']['editor_type']);
        $this->assertArrayNotHasKey('type', $data['config']['mode']);
        $this->assertArrayNotHasKey('notes', $data);
        $this->assertArrayHasKey('fields', $data['config']);
        $this->assertSame('visible', $data['common']['visibility']['default']);
        $this->assertArrayHasKey('instructions', $data['common']);
        $this->assertArrayNotHasKey('mode', $data['common']);
        $this->assertSame([
            ['path' => ['common', 'handle', 'validate', 1], 'source' => 'php', 'php_type' => 'Statamic\\Rules\\Handle'],
        ], $data['unresolved']);
    }

    #[Test]
    public function it_includes_bard_toolbar_defaults_and_conditional_configuration()
    {
        $data = $this->commandJson(['handle' => 'bard']);

        $this->assertContains('bold', $data['config']['buttons']['default']);
        $this->assertSame('bard_buttons_setting', $data['config']['buttons']['editor_type']);
        $this->assertArrayNotHasKey('options', $data['config']['buttons']);
        $this->assertContains(['path' => ['config', 'buttons', 'options'], 'source' => 'editor'], $data['unresolved']);
        $this->assertSame(['buttons' => 'contains_any anchor, image'], $data['config']['container']['if']);
        $this->assertSame('fixed', $data['config']['toolbar_mode']['default']);

        $data = $this->commandJson(['handle' => 'markdown']);

        $this->assertSame('markdown_buttons_setting', $data['config']['buttons']['editor_type']);
        $this->assertArrayNotHasKey('options', $data['config']['buttons']);
        $this->assertContains(['path' => ['config', 'buttons', 'options'], 'source' => 'editor'], $data['unresolved']);
    }

    #[Test]
    public function it_includes_addon_configuration_and_marks_unresolved_values_without_running_them()
    {
        app('statamic.fieldtypes')->put('test_inspection', InspectionFieldtype::class);
        InspectionFieldtype::appendConfigField('extra', ['type' => 'toggle', 'default' => false]);

        $data = $this->commandJson(['handle' => 'test_inspection']);

        $this->assertSame(['editor_type' => 'toggle', 'default' => false], $data['config']['extra']);
        $this->assertSame(['enabled' => 'Enabled'], $data['config']['mode']['options']);
        $this->assertSame(['mode' => 'enabled'], $data['config']['limit']['if']);
        $this->assertSame(0, $data['config']['limit']['default']);
        $this->assertNull($data['config']['optional']['default']);
        $this->assertNull($data['config']['dynamic']['options']);
        $this->assertNull($data['config']['nested']['default']['value']);
        $this->assertSame('<fg=red>Inspection</>', $data['fieldtype']['title']);
        $this->assertSame('text', $data['config']['nested']['editor_type']);
        $this->assertSame('content_block', $data['config']['nested']['default']['type']);
        $this->assertContains(['path' => ['config', 'dynamic', 'options'], 'source' => 'php', 'php_type' => 'Closure'], $data['unresolved']);
        $this->assertContains(['path' => ['config', 'nested', 'default', 'value'], 'source' => 'php', 'php_type' => 'stdClass'], $data['unresolved']);
    }

    #[Test]
    public function it_preserves_registered_aliases()
    {
        app('statamic.fieldtypes')->put('test_alias', InspectionFieldtype::class);

        $data = $this->commandJson(['handle' => 'test_alias']);

        $this->assertSame('test_alias', $data['fieldtype']['handle']);
        $this->assertSame(InspectionFieldtype::class, $data['fieldtype']['class']);
    }

    #[Test]
    public function it_can_inspect_every_registered_core_fieldtype()
    {
        $inspector = app(FieldtypeInspector::class);

        foreach (app('statamic.fieldtypes') as $handle => $class) {
            if (! str_starts_with($class, 'Statamic\\Fieldtypes\\')) {
                continue;
            }

            $data = $inspector->show($handle);

            $this->assertSame($handle, $data['fieldtype']['handle']);
            $this->assertJson(json_encode($data, JSON_THROW_ON_ERROR));
        }
    }

    #[Test]
    public function it_can_show_human_readable_configuration()
    {
        $this->assertSame(0, Artisan::call('statamic:fieldtypes:show', ['handle' => 'grid']));

        $data = Yaml::parse(Artisan::output());

        $this->assertSame('grid', $data['fieldtype']['handle']);
        $this->assertSame('table', $data['config']['mode']['default']);
    }

    #[Test]
    public function an_unknown_fieldtype_returns_a_nonzero_exit_code_and_json_error()
    {
        $data = $this->commandJson(['handle' => 'does_not_exist'], 1);

        $this->assertSame(['error' => 'Fieldtype [does_not_exist] not found'], $data);
    }

    private function commandJson(array $arguments, int $exitCode = 0): array
    {
        $this->assertSame($exitCode, Artisan::call('statamic:fieldtypes:show', $arguments + ['--json' => true]));

        return json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);
    }
}

class InspectionFieldtype extends Fieldtype
{
    protected static $title = '<fg=red>Inspection</>';

    protected function configFieldItems(): array
    {
        return [
            'mode' => [
                'type' => 'select',
                'options' => collect(['enabled' => 'Enabled']),
            ],
            'limit' => [
                'type' => 'integer',
                'default' => 0,
                'if' => ['mode' => 'enabled'],
            ],
            'optional' => [
                'type' => 'text',
                'default' => null,
            ],
            'dynamic' => [
                'type' => 'select',
                'options' => function () {
                    throw new \RuntimeException('This callback must not run during inspection.');
                },
            ],
            'nested' => [
                'type' => 'text',
                'default' => ['type' => 'content_block', 'value' => new \stdClass],
            ],
        ];
    }
}
