<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ConfigureFormFieldTest extends TestCase
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
        Form::make('other')->set('unique_instances', true)->save();
    }

    #[Test]
    #[DataProvider('layouts')]
    public function an_entry_editor_without_form_permission_keeps_the_stored_config_on_save($layout)
    {
        [$user, $collection] = $this->seedUserAndCollection(['access cp', 'edit test entries', 'access en site']);
        $this->setBlueprint($collection, $layout);

        $entry = EntryFactory::collection($collection)->slug('a')->data(['title' => 'A', ...$this->wrap($layout, self::STORED)])->create();

        $this->actingAs($user)
            ->patchJson($entry->updateUrl(), [
                'title' => 'Updated',
                'slug' => 'a',
                ...$this->wrap($layout, ['submission_limit' => 99, 'connections' => ['webhook' => [['id' => 'evil', 'url' => 'https://evil.test']]]]),
            ])
            ->assertOk();

        $this->assertSame('Updated', Entry::find($entry->id())->get('title'));
        $this->assertEquals($this->wrap($layout, self::STORED), $this->wrapStored(Entry::find($entry->id()), $layout));
    }

    #[Test]
    #[DataProvider('layouts')]
    public function changing_the_form_clears_the_config_for_an_entry_editor_without_form_permission($layout)
    {
        [$user, $collection] = $this->seedUserAndCollection(['access cp', 'edit test entries', 'access en site']);
        $this->setBlueprint($collection, $layout);

        $entry = EntryFactory::collection($collection)->slug('a')->data(['title' => 'A', ...$this->wrap($layout, self::STORED)])->create();

        $this->actingAs($user)
            ->patchJson($entry->updateUrl(), [
                'title' => 'A',
                'slug' => 'a',
                ...$this->wrap($layout, ['submission_limit' => 99], 'other'),
            ])
            ->assertOk();

        $this->assertEquals($this->wrap($layout, null, 'other'), $this->wrapStored(Entry::find($entry->id()), $layout));
    }

    #[Test]
    public function an_editor_with_form_permission_saves_the_incoming_config()
    {
        [$user, $collection] = $this->seedUserAndCollection(['access cp', 'edit test entries', 'access en site', 'edit contact form']);
        $this->setBlueprint($collection, 'top');

        $entry = EntryFactory::collection($collection)->slug('a')->data(['title' => 'A', ...$this->wrap('top', self::STORED)])->create();

        $this->actingAs($user)
            ->patchJson($entry->updateUrl(), [
                'title' => 'A',
                'slug' => 'a',
                ...$this->wrap('top', ['submission_limit' => 99]),
            ])
            ->assertOk();

        $this->assertEquals(
            $this->wrap('top', ['submission_limit' => 99]),
            $this->wrapStored(Entry::find($entry->id()), 'top')
        );
    }

    public static function layouts(): array
    {
        return ['top level' => ['top'], 'replicator' => ['replicator'], 'grid' => ['grid']];
    }

    private function wrap(string $layout, ?array $config, string $form = 'contact'): array
    {
        $value = $config ? ['form' => $form, 'config' => $config] : $form;

        return match ($layout) {
            'top' => ['rsvp' => $value],
            'replicator' => ['blocks' => [['id' => 'one', 'type' => 'set', 'enabled' => true, 'rsvp' => $value]]],
            'grid' => ['rows' => [['rsvp' => $value]]],
        };
    }

    private function wrapStored($entry, string $layout): array
    {
        $data = $entry->data()->all();

        return match ($layout) {
            'top' => ['rsvp' => $data['rsvp']],
            'replicator' => ['blocks' => [['id' => 'one', 'type' => 'set', 'enabled' => true, 'rsvp' => $data['blocks'][0]['rsvp']]]],
            'grid' => ['rows' => [['rsvp' => $data['rows'][0]['rsvp']]]],
        };
    }

    private function setBlueprint($collection, string $layout): void
    {
        $form = ['handle' => 'rsvp', 'field' => ['type' => 'form', 'max_items' => 1]];

        $fields = [['handle' => 'title', 'field' => ['type' => 'text']]];

        $fields[] = match ($layout) {
            'top' => $form,
            'replicator' => ['handle' => 'blocks', 'field' => ['type' => 'replicator', 'sets' => ['main' => ['sets' => ['set' => ['fields' => [$form]]]]]]],
            'grid' => ['handle' => 'rows', 'field' => ['type' => 'grid', 'fields' => [$form]]],
        };

        $blueprint = Blueprint::makeFromFields([])->setContents(['tabs' => ['main' => ['sections' => [['fields' => $fields]]]]]);

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('in')
            ->with('collections/'.$collection->handle())
            ->andReturn(collect([$blueprint->setHandle('test')]));
    }

    private function seedUserAndCollection(array $permissions): array
    {
        $this->setTestRoles(['test' => $permissions]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();

        return [$user, $collection];
    }
}
