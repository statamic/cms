<?php

namespace Tests\Forms;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Form;
use Statamic\Forms\ConfigFields;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormInstancesInjectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    protected function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true)->byDefault();

        Collection::make('events')->save();
        Collection::make('pages')->save();
    }

    private function blueprint(string $collection, array $fields = [])
    {
        Blueprint::make('default')->setNamespace('collections.'.$collection)->setContents(['tabs' => [
            'main' => ['sections' => [['fields' => [
                ['handle' => 'title', 'field' => ['type' => 'text']],
                ...$fields,
            ]]]],
        ]])->save();

        Blink::flush();

        return Collection::find($collection)->entryBlueprint();
    }

    #[Test]
    public function it_injects_the_field_into_the_sidebar_of_targeted_collections()
    {
        Form::make('rsvp')->set('unique_instances', true)->set('unique_instances_collections', ['events'])->save();

        $blueprint = $this->blueprint('events');

        $this->assertTrue($blueprint->hasFieldInTab('form_instances', 'sidebar'));
        $this->assertEquals('form_instances', $blueprint->field('form_instances')->type());
        $this->assertEquals('events', $blueprint->field('form_instances')->get('collection'));
        $this->assertEquals('Forms', $blueprint->field('form_instances')->display());

        $this->assertFalse($this->blueprint('pages')->hasField('form_instances'));
    }

    #[Test]
    public function it_doesnt_inject_the_field_without_unique_instances()
    {
        Form::make('rsvp')->set('unique_instances', false)->set('unique_instances_collections', ['events'])->save();

        $this->assertFalse($this->blueprint('events')->hasField('form_instances'));
    }

    #[Test]
    public function it_doesnt_inject_the_field_without_forms_pro()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false);

        Form::make('rsvp')->set('unique_instances', true)->set('unique_instances_collections', ['events'])->save();

        $this->assertFalse($this->blueprint('events')->hasField('form_instances'));
    }

    #[Test]
    public function it_doesnt_duplicate_an_existing_form_instances_field()
    {
        Form::make('rsvp')->set('unique_instances', true)->set('unique_instances_collections', ['events'])->save();

        $blueprint = $this->blueprint('events', [
            ['handle' => 'form_instances', 'field' => ['type' => 'form_instances', 'display' => 'RSVP']],
        ]);

        $this->assertFalse($blueprint->hasFieldInTab('form_instances', 'sidebar'));
        $this->assertEquals('RSVP', $blueprint->field('form_instances')->display());
        $this->assertCount(1, $blueprint->fields()->all()->filter(fn ($field) => $field->type() === 'form_instances'));
    }

    #[Test]
    public function the_collections_config_field_is_shown_when_unique_instances_is_on()
    {
        $field = ConfigFields::fields()['submissions']['fields']['unique_instances_collections'];

        $this->assertEquals('collections', $field['type']);
        $this->assertEquals('select', $field['mode']);
        $this->assertEquals(['unique_instances' => 'equals true'], $field['if']);
    }

    #[Test]
    public function the_collections_config_field_requires_forms_pro()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(false);
        Blink::flush();

        $this->assertArrayNotHasKey('unique_instances_collections', ConfigFields::fields()['submissions']['fields']);
    }

    #[Test]
    public function the_form_only_reports_collections_while_unique_instances_is_on()
    {
        $form = Form::make('rsvp')->set('unique_instances', true)->set('unique_instances_collections', ['events']);

        $this->assertEquals(['events'], $form->uniqueInstancesCollections());

        $form->set('unique_instances', false);

        $this->assertEquals([], $form->uniqueInstancesCollections());
    }
}
