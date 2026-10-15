<?php

namespace Tests\Fieldtypes;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

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
        Form::make('other')->save();
    }

    private function field(array $config = []): Field
    {
        return new Field('rsvp_form', array_merge(['type' => 'form', 'max_items' => 1], $config));
    }

    private function entry()
    {
        return Entry::make()->id('entry-id')->collection(tap(Collection::make('pages'))->save());
    }

    #[Test]
    public function it_processes_to_plain_handles()
    {
        $this->assertEquals('contact', $this->field()->fieldtype()->process(['contact']));
        $this->assertEquals(['contact', 'other'], $this->field(['max_items' => 2])->fieldtype()->process(['contact', 'other']));
        $this->assertNull($this->field()->fieldtype()->process([]));
    }

    #[Test]
    public function it_pre_processes_to_plain_handles()
    {
        $this->assertEquals(['contact'], $this->field()->fieldtype()->preProcess('contact'));
        $this->assertEquals(['contact', 'other'], $this->field(['max_items' => 2])->fieldtype()->preProcess(['contact', 'other']));
    }

    #[Test]
    public function it_doesnt_preload_configure_meta()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $preload = $this->field()->setParent($this->entry())->setValue(['contact'])->fieldtype()->preload();

        $this->assertEquals('contact', $preload['data'][0]['id']);
        $this->assertArrayNotHasKey('configureMeta', $preload);
        $this->assertArrayNotHasKey('configurable', $preload);
    }

    #[Test]
    public function it_only_preloads_submissions_when_a_single_form_is_allowed()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $preload = fn (int $maxItems) => $this->field(['max_items' => $maxItems])
            ->setParent($this->entry())
            ->setValue(['contact'])
            ->fieldtype()
            ->preload();

        $this->assertEquals('contact', $preload(1)['submissions']['form']);
        $this->assertArrayNotHasKey('submissions', $preload(2));
    }

    #[Test]
    public function it_only_preloads_submissions_for_unique_instances_forms_on_an_entry()
    {
        $this->actingAs(tap(User::make()->makeSuper())->save());

        $preload = fn ($form, $parent) => $this->field()->setParent($parent)->setValue([$form])->fieldtype()->preload();

        $this->assertArrayHasKey('submissions', $preload('contact', $this->entry()));
        $this->assertArrayNotHasKey('submissions', $preload('other', $this->entry()));
        $this->assertArrayNotHasKey('submissions', $preload('contact', null));
    }

    #[Test]
    public function it_only_preloads_submissions_for_users_who_can_view_submissions()
    {
        $this->setTestRoles(['editor' => ['access cp', 'edit contact form'], 'viewer' => ['access cp', 'view contact form submissions']]);

        $preload = fn () => $this->field()->setParent($this->entry())->setValue(['contact'])->fieldtype()->preload();

        $this->actingAs(tap(User::make()->assignRole('editor'))->save());
        $this->assertArrayNotHasKey('submissions', $preload());

        $this->actingAs(tap(User::make()->assignRole('viewer'))->save());
        $this->assertArrayHasKey('submissions', $preload());
    }

    #[Test]
    public function it_adds_a_submissions_url_to_index_items_for_users_who_can_view_submissions()
    {
        $this->setTestRoles(['editor' => ['access cp', 'edit contact form'], 'viewer' => ['access cp', 'view contact form submissions']]);

        $index = fn () => $this->field(['max_items' => 2])->setParent($this->entry())->fieldtype()->preProcessIndex(['contact', 'other'])->keyBy('id');

        $this->actingAs(tap(User::make()->assignRole('editor'))->save());
        $this->assertArrayNotHasKey('submissions_url', $index()['contact']);

        $this->actingAs(tap(User::make()->assignRole('viewer'))->save());
        $this->assertStringStartsWith(Form::find('contact')->submissionsUrl().'?filters=', $index()['contact']['submissions_url']);
        $this->assertArrayNotHasKey('submissions_url', $index()['other']);
    }

    #[Test]
    public function it_augments_to_the_form()
    {
        $this->assertEquals('contact', $this->field()->fieldtype()->augment('contact')->handle());
        $this->assertEquals(['contact', 'other'], $this->field(['max_items' => 2])->fieldtype()->augment(['contact', 'other'])->map->handle()->all());
    }
}
