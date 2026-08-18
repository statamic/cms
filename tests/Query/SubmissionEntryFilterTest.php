<?php

namespace Tests\Query;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\Scope;
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
}
