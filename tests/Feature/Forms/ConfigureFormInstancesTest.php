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

    private const DRIFTED = [
        'id' => 'drift',
        'to' => ['field:email'],
        'conditions' => [['field' => 'name', 'operator' => 'equals', 'value' => 'Bob']],
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

        Form::make('contact')->set('unique_instances', true)->set('unique_instances_collections', ['test'])->save();
        Form::make('feedback')->set('unique_instances', true)->set('unique_instances_collections', ['test'])->save();
        Form::make('other')->set('unique_instances', true)->set('unique_instances_collections', ['test'])->save();
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
    public function an_entry_editor_without_form_permission_keeps_the_working_copy_config_on_save()
    {
        config(['statamic.revisions.enabled' => true]);

        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site'], ['contact' => []]);
        $entry->collection()->revisionsEnabled(true)->save();

        tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['form_instances'] = ['contact' => self::STORED];
            $copy->attributes($attrs);
        })->save();

        $this->save($user, $entry, ['forms' => ['contact'], 'config' => ['contact' => []]]);

        $entry = Entry::find($entry->id());

        $this->assertSame('A', $entry->get('title'));
        $this->assertEquals(['contact' => []], $entry->get('form_instances'));
        $this->assertSame('Updated', $entry->fromWorkingCopy()->get('title'));
        $this->assertEquals(['contact' => self::STORED], $entry->fromWorkingCopy()->get('form_instances'));
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
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'view other form submissions'], [
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
    public function an_entry_editor_cant_add_a_form_they_cant_view()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'view contact form submissions'], null);

        $this->save($user, $entry, ['forms' => ['contact', 'other'], 'config' => ['contact' => [], 'other' => []]]);

        $this->assertSame(['contact' => []], Entry::find($entry->id())->get('form_instances'));
    }

    #[Test]
    public function an_entry_editor_cant_add_a_form_that_doesnt_target_the_collection()
    {
        Form::make('elsewhere')->set('unique_instances', true)->set('unique_instances_collections', ['events'])->save();

        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit forms'], null);

        $this->save($user, $entry, ['forms' => ['contact', 'elsewhere'], 'config' => ['contact' => [], 'elsewhere' => []]]);

        $this->assertSame(['contact' => []], Entry::find($entry->id())->get('form_instances'));
    }

    #[Test]
    public function an_entry_editor_keeps_a_stored_form_they_couldnt_add()
    {
        Form::make('elsewhere')->set('unique_instances', true)->set('unique_instances_collections', ['events'])->save();

        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site'], [
            'contact' => self::STORED,
            'elsewhere' => [],
        ]);

        $this->save($user, $entry, ['forms' => ['contact', 'elsewhere'], 'config' => ['contact' => [], 'elsewhere' => []]]);

        $this->assertEquals(['contact' => self::STORED, 'elsewhere' => []], Entry::find($entry->id())->get('form_instances'));
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

    #[Test]
    public function an_editor_with_form_permission_cant_save_an_invalid_connection_override()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit contact form'], ['contact' => self::STORED]);

        $this->update($user, $entry, ['forms' => ['contact'], 'config' => [
            'contact' => ['connections' => ['email' => [['to' => 'admin@example.com', 'html' => 'statamic::auth.login']]]],
        ]])->assertUnprocessable()->assertJsonValidationErrors(['form_instances' => 'Invalid connections.']);

        $this->assertSame('A', Entry::find($entry->id())->get('title'));
        $this->assertEquals(['contact' => self::STORED], Entry::find($entry->id())->get('form_instances'));
    }

    #[Test]
    public function an_editor_with_form_permission_cant_save_an_invalid_access_override()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit contact form'], ['contact' => self::STORED]);

        $this->update($user, $entry, ['forms' => ['contact'], 'config' => [
            'contact' => ['submission_limit' => 'lots'],
        ]])->assertUnprocessable()->assertJsonValidationErrors('form_instances');

        $this->assertSame('A', Entry::find($entry->id())->get('title'));
        $this->assertEquals(['contact' => self::STORED], Entry::find($entry->id())->get('form_instances'));
    }

    #[Test]
    public function an_entry_editor_without_form_permission_isnt_blocked_by_an_invalid_override()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site'], ['contact' => self::STORED]);

        $this->save($user, $entry, ['forms' => ['contact'], 'config' => [
            'contact' => ['submission_limit' => 'lots', 'connections' => ['email' => [['to' => 'admin@example.com', 'html' => 'statamic::auth.login']]]],
        ]]);

        $this->assertSame('Updated', Entry::find($entry->id())->get('title'));
        $this->assertEquals(['contact' => self::STORED], Entry::find($entry->id())->get('form_instances'));
    }

    #[Test]
    public function an_unchanged_drifted_connection_override_doesnt_block_a_save()
    {
        $stored = ['connections' => ['email' => [self::DRIFTED]]];

        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit contact form'], ['contact' => $stored]);

        $this->save($user, $entry, $this->posted($user, $entry));

        $this->assertSame('Updated', Entry::find($entry->id())->get('title'));
        $this->assertEquals(['contact' => $stored], Entry::find($entry->id())->get('form_instances'));
    }

    #[Test]
    public function a_changed_drifted_connection_override_is_still_validated()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit contact form'], [
            'contact' => ['connections' => ['email' => [self::DRIFTED]]],
        ]);

        $posted = $this->posted($user, $entry);
        $posted['config']['contact']['connections']['email'][0]['subject'] = 'Changed';

        $this->update($user, $entry, $posted)->assertUnprocessable()->assertJsonValidationErrors(['form_instances' => 'Invalid connections.']);
    }

    #[Test]
    public function a_new_invalid_connection_next_to_an_unchanged_drifted_one_is_validated()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit contact form'], [
            'contact' => ['connections' => ['email' => [self::DRIFTED]]],
        ]);

        $posted = $this->posted($user, $entry);
        $posted['config']['contact']['connections']['email'][] = ['to' => ['admin@example.com'], 'html' => 'statamic::auth.login'];

        $this->update($user, $entry, $posted)->assertUnprocessable()->assertJsonValidationErrors(['form_instances' => 'Invalid connections.']);
    }

    #[Test]
    public function a_changed_connection_next_to_an_unchanged_drifted_one_can_be_saved()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit contact form'], [
            'contact' => ['connections' => ['email' => [self::DRIFTED, ['id' => 'ok', 'to' => ['admin@example.com']]]]],
        ]);

        $posted = $this->posted($user, $entry);
        $posted['config']['contact']['connections']['email'][1]['to'] = ['other@example.com'];

        $this->save($user, $entry, $posted);

        $this->assertEquals(
            ['contact' => ['connections' => ['email' => [self::DRIFTED, ['id' => 'ok', 'to' => ['other@example.com']]]]]],
            Entry::find($entry->id())->get('form_instances')
        );
    }

    #[Test]
    public function an_unchanged_invalid_access_override_doesnt_block_a_save()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit contact form'], [
            'contact' => ['submission_limit' => 'lots'],
        ]);

        $this->save($user, $entry, ['forms' => ['contact'], 'config' => ['contact' => ['submission_limit' => 'lots']]]);

        $this->assertSame('Updated', Entry::find($entry->id())->get('title'));
    }

    #[Test]
    public function a_changed_invalid_access_override_is_still_validated()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit contact form'], [
            'contact' => ['submission_limit' => 'lots'],
        ]);

        $this->update($user, $entry, ['forms' => ['contact'], 'config' => ['contact' => ['submission_limit' => 'many']]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('form_instances');
    }

    #[Test]
    public function an_unchanged_drifted_override_doesnt_block_a_localization_save()
    {
        [$user, $origin, $localization] = $this->seedLocalization(['contact' => ['connections' => ['email' => [self::DRIFTED]]]]);

        $this->update($user, $localization, $this->posted($user, $localization), ['_localized' => ['title']])->assertOk();

        $this->assertSame('Updated', Entry::find($localization->id())->get('title'));
        $this->assertEquals(['contact' => ['connections' => ['email' => [self::DRIFTED]]]], Entry::find($origin->id())->get('form_instances'));
        $this->assertNull(Entry::find($localization->id())->get('form_instances'));
    }

    #[Test]
    public function a_changed_override_is_validated_on_a_localization()
    {
        [$user, $origin, $localization] = $this->seedLocalization(['contact' => self::STORED]);

        $invalid = ['forms' => ['contact'], 'config' => [
            'contact' => ['connections' => ['email' => [['to' => ['admin@example.com'], 'html' => 'statamic::auth.login']]]],
        ]];

        $this->update($user, $localization, $invalid, ['_localized' => ['title']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['form_instances' => 'Invalid connections.']);

        $this->update($user, $localization, $invalid, ['_localized' => ['title', 'form_instances']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['form_instances' => 'Invalid connections.']);

        $this->assertSame('A', Entry::find($localization->id())->get('title'));
        $this->assertNull(Entry::find($localization->id())->get('form_instances'));
    }

    #[Test]
    public function an_invalid_date_override_error_lands_on_the_field()
    {
        [$user, $entry] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'edit contact form'], ['contact' => self::STORED]);

        $this->update($user, $entry, ['forms' => ['contact'], 'config' => ['contact' => ['close_date' => 'bad-date']]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('form_instances')
            ->assertJsonMissingValidationErrors('close_date');

        $this->assertEquals(['contact' => self::STORED], Entry::find($entry->id())->get('form_instances'));
    }

    private function save($user, $entry, array $formInstances): void
    {
        $this->update($user, $entry, $formInstances)->assertOk();
    }

    private function update($user, $entry, array $formInstances, array $extra = [])
    {
        return $this->actingAs($user)->patchJson($entry->updateUrl(), [
            'title' => 'Updated',
            'slug' => 'a',
            'form_instances' => $formInstances,
            ...$extra,
        ]);
    }

    private function posted($user, $entry): array
    {
        $this->actingAs($user);

        return $entry->blueprint()->field('form_instances')
            ->setParent($entry)
            ->setValue($entry->value('form_instances'))
            ->preProcess()
            ->value();
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

    private function seedLocalization(?array $formInstances): array
    {
        $this->setSites([
            'en' => ['locale' => 'en', 'url' => '/'],
            'fr' => ['locale' => 'fr', 'url' => '/fr/'],
        ]);

        [$user, $origin] = $this->seedEntry(['access cp', 'edit test entries', 'access en site', 'access fr site', 'edit contact form'], $formInstances);
        $origin->collection()->sites(['en', 'fr'])->save();

        $localization = EntryFactory::collection($origin->collection())->locale('fr')->origin($origin)->slug('a')->data(['title' => 'A'])->create();

        return [$user, $origin, $localization];
    }
}
