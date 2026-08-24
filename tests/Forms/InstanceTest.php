<?php

namespace Tests\Forms;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Form;
use Statamic\Forms\Instance;
use Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class InstanceTest extends TestCase
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

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturn(true);
    }

    private function makeForm(array $data = [])
    {
        return tap(Form::make('contact')->data($data))->save();
    }

    private function makeEntry(string $id, array $formValue): void
    {
        Blueprint::make('event')->setNamespace('collections.events')->setContents(['fields' => [
            ['handle' => 'title', 'field' => ['type' => 'text']],
            ['handle' => 'rsvp_form', 'field' => ['type' => 'form', 'max_items' => 1]],
        ]])->save();

        (new EntryFactory)->collection('events')->id($id)->slug($id)->data(['rsvp_form' => $formValue])->create();
    }

    #[Test]
    public function a_form_makes_instances()
    {
        $form = $this->makeForm();

        $instance = $form->instance('event-1');

        $this->assertInstanceOf(Instance::class, $instance);
        $this->assertEquals($form->handle(), $instance->form()->handle());
        $this->assertEquals('event-1', $instance->entry());

        $this->assertNull($form->instance()->entry());
    }

    #[Test]
    public function the_default_instance_reads_the_forms_config()
    {
        $form = $this->makeForm(['submission_limit' => 5]);

        $this->assertEquals(5, $form->instance()->config('submission_limit'));
        $this->assertNull($form->instance()->config('close_date'));
    }

    #[Test]
    public function an_entry_instance_prefers_the_entrys_overrides()
    {
        $form = $this->makeForm(['submission_limit' => 5, 'closed_message' => 'Closed.']);

        $this->makeEntry('event-1', ['form' => 'contact', 'config' => ['submission_limit' => 1]]);

        $instance = $form->instance('event-1');

        $this->assertEquals(1, $instance->config('submission_limit'));
        $this->assertEquals('Closed.', $instance->config('closed_message'));
    }

    #[Test]
    public function a_localization_inherits_overrides_from_its_origin()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        $form = $this->makeForm(['submission_limit' => 5]);

        $this->makeEntry('event-1', ['form' => 'contact', 'config' => ['submission_limit' => 1]]);

        Collection::findByHandle('events')->sites(['en', 'fr'])->save();

        tap(Entry::find('event-1')->makeLocalization('fr')->id('event-1-fr'))->save();

        $this->assertEquals(1, $form->instance('event-1-fr')->config('submission_limit'));
    }

    #[Test]
    public function overrides_from_an_entry_using_a_different_form_are_ignored()
    {
        $form = $this->makeForm(['submission_limit' => 5]);

        $this->makeEntry('event-1', ['form' => 'another_form', 'config' => ['submission_limit' => 1]]);

        $this->assertEquals(5, $form->instance('event-1')->config('submission_limit'));
    }

    #[Test]
    public function an_unconfigured_entry_falls_back_to_the_forms_config()
    {
        $form = $this->makeForm(['submission_limit' => 5]);

        $this->makeEntry('event-1', ['form' => 'contact', 'config' => []]);

        $this->assertEquals(5, $form->instance('event-1')->config('submission_limit'));
    }

    #[Test]
    public function the_form_delegates_to_its_default_instance()
    {
        $form = $this->makeForm(['close_date' => '2020-01-01 09:00']);

        $this->assertEquals('closed', $form->status());
        $this->assertTrue($form->restricted());
        $this->assertEquals('This form is no longer accepting submissions.', $form->restrictionMessage());
    }
}
