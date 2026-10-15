<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Facades\YAML;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ConfigureFormInstancesTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private const STORED = [
        'submission_limit' => 5,
        'connections' => ['webhook' => [['id' => 'abc', 'url' => 'https://example.com/hook']]],
    ];

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    public function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true)->byDefault();

        Form::make('contact')->set('unique_instances', true)->save();
        Form::make('feedback')->set('unique_instances', true)->save();
        Form::make('other')->set('unique_instances', true)->save();
    }

    #[Test]
    public function an_entry_editor_without_form_permission_keeps_the_stored_config_on_save()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site'], ['contact' => self::STORED]);

        $this->save($user, $entry, ['forms' => ['contact'], 'config' => [
            'contact' => ['submission_limit' => 99, 'connections' => ['webhook' => [['id' => 'evil', 'url' => 'https://evil.test']]]],
        ]]);

        $this->assertSame('Updated', Entry::find($entry->id())->get('title'));
        $this->assertEquals(['contact' => self::STORED], Entry::find($entry->id())->get('form_instances'));
    }

    #[Test]
    public function an_entry_editor_without_form_permission_keeps_each_forms_own_config()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site'], [
            'contact' => ['submission_limit' => 5],
            'feedback' => ['submission_limit' => 99],
        ]);

        $this->save($user, $entry, ['forms' => ['feedback', 'contact'], 'config' => ['feedback' => [], 'contact' => []]]);

        $this->assertSame([
            'feedback' => ['submission_limit' => 99],
            'contact' => ['submission_limit' => 5],
        ], Entry::find($entry->id())->get('form_instances'));
    }

    #[Test]
    public function an_entry_editor_without_form_permission_can_add_and_remove_forms()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site'], [
            'contact' => self::STORED,
            'feedback' => ['submission_limit' => 99],
        ]);

        $this->save($user, $entry, ['forms' => ['contact', 'other'], 'config' => [
            'contact' => [],
            'other' => ['submission_limit' => 1],
        ]]);

        $this->assertEquals(['contact' => self::STORED, 'other' => []], Entry::find($entry->id())->get('form_instances'));
    }

    #[Test]
    public function an_editor_with_form_permission_saves_the_incoming_config()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit contact form'], [
            'contact' => self::STORED,
            'feedback' => ['submission_limit' => 99],
        ]);

        $this->save($user, $entry, ['forms' => ['contact', 'feedback'], 'config' => [
            'contact' => ['submission_limit' => 10],
            'feedback' => ['submission_limit' => 1],
        ]]);

        $this->assertEquals([
            'contact' => ['submission_limit' => 10],
            'feedback' => ['submission_limit' => 99],
        ], Entry::find($entry->id())->get('form_instances'));
    }

    #[Test]
    public function a_form_without_overrides_survives_a_save()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit contact form'], null);

        $this->save($user, $entry, ['forms' => ['contact'], 'config' => ['contact' => []]]);

        $entry = Entry::find($entry->id());

        $this->assertSame(['contact' => []], $entry->get('form_instances'));
        $this->assertSame(['contact' => []], YAML::parse($entry->fileContents())['form_instances']);
    }

    #[Test]
    public function the_stored_config_survives_a_save_without_forms_pro()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false);
        Blink::forget('forms-pro-installed');

        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit contact form'], [
            'contact' => self::STORED,
            'feedback' => ['submission_limit' => 99],
        ]);

        $this->save($user, $entry, ['forms' => ['contact'], 'config' => ['contact' => []]]);

        $this->assertEquals(['contact' => self::STORED], Entry::find($entry->id())->get('form_instances'));
    }

    private function save($user, $entry, array $formInstances): void
    {
        $this->actingAs($user)
            ->patchJson($entry->updateUrl(), [
                'title' => 'Updated',
                'slug' => 'a',
                'form_instances' => $formInstances,
            ])
            ->assertOk();
    }

    private function seedEntry(array $permissions, ?array $formInstances): array
    {
        $this->setTestRoles(['test' => $permissions]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();

        $blueprint = Blueprint::makeFromFields([])->setContents(['tabs' => ['main' => ['sections' => [['fields' => [
            ['handle' => 'title', 'field' => ['type' => 'text']],
            ['handle' => 'form_instances', 'field' => ['type' => 'form_instances']],
        ]]]]]]);

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('in')
            ->with('collections/'.$collection->handle())
            ->andReturn(collect([$blueprint->setHandle('test')]));

        $entry = EntryFactory::collection($collection)->slug('a')->data(['title' => 'A', 'form_instances' => $formInstances])->create();

        return [$user, $entry];
    }
}
