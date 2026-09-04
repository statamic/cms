<?php

namespace Tests\Query;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\Scope;
use Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SubmissionEntryFilterTest extends TestCase
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

        Form::make('test')->set('unique_instances', true)->save();
    }

    private function filter(array $context = [])
    {
        return Scope::find('submission_entry', array_merge(['form' => 'test'], $context));
    }

    #[Test]
    public function it_is_only_visible_when_the_form_has_unique_instances()
    {
        $this->assertTrue($this->filter()->visibleTo('form-submissions'));
        $this->assertFalse($this->filter()->visibleTo('entries'));

        Form::find('test')->set('unique_instances', false)->save();

        $this->assertFalse($this->filter()->visibleTo('form-submissions'));
    }

    #[Test]
    public function it_auto_applies_when_the_context_provides_an_entry()
    {
        $this->assertEquals(['entry' => 'event-1'], $this->filter(['entry' => 'event-1'])->autoApply());
        $this->assertEquals([], $this->filter()->autoApply());
    }

    #[Test]
    public function it_offers_the_entries_with_submissions_as_options()
    {
        (new EntryFactory)->collection('events')->id('event-1')->slug('event-one')->data(['title' => 'Event One'])->create();
        (new EntryFactory)->collection('events')->id('event-2')->slug('event-two')->data(['title' => 'Event Two'])->create();
        (new EntryFactory)->collection('events')->id('event-3')->slug('event-three')->data(['title' => 'Event Three'])->create();

        $form = Form::find('test');

        FormSubmission::make()->form($form)->id('1')->data(['entry' => 'event-1'])->save();
        FormSubmission::make()->form($form)->id('2')->data(['entry' => 'event-1'])->save();
        FormSubmission::make()->form($form)->id('3')->data(['entry' => 'event-2'])->save();
        FormSubmission::make()->form($form)->id('4')->data([])->save();

        $this->assertEquals([
            'event-1' => 'Event One',
            'event-2' => 'Event Two',
        ], $this->filter()->fieldItems()['entry']['options']);
    }

    #[Test]
    public function it_renders_a_badge_with_the_entrys_title()
    {
        (new EntryFactory)->collection('events')->id('event-1')->slug('event-one')->data(['title' => 'Event One'])->create();

        $this->assertEquals('Entry: Event One', $this->filter()->badge(['entry' => 'event-1']));
        $this->assertEquals('Entry: missing', $this->filter()->badge(['entry' => 'missing']));
    }
}
