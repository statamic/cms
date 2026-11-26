<?php

namespace Tests\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\CommandPalette\Link;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Forms\Exceptions\BlueprintUndefinedException;
use Statamic\Forms\Tags;
use Statamic\Policies\FormSubmissionPolicy;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DeprecatedApiTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function form_blueprint_command_palette_link_still_works()
    {
        $form = tap(Form::make('contact'))->save();

        $link = $form->blueprintCommandPaletteLink();

        $this->assertInstanceOf(Link::class, $link);
        $this->assertEquals($form->editBlueprintUrl(), $link->toArray()['url']);
    }

    #[Test]
    public function form_tags_event_url_still_works()
    {
        $this->assertEquals('http://localhost/!/form/contact', (new Tags)->eventUrl('contact'));
    }

    #[Test]
    public function blueprint_undefined_exception_still_exists()
    {
        $form = Form::make('contact');

        $exception = BlueprintUndefinedException::create($form);

        $this->assertEquals('Form [contact] does not have a blueprint', $exception->getMessage());
    }

    #[Test]
    public function form_submission_policy_subclasses_can_call_parent_before()
    {
        $this->setTestRoles(['configurer' => ['configure forms']]);

        $policy = new class extends FormSubmissionPolicy
        {
            public function before($user, $ability)
            {
                return parent::before($user, $ability);
            }
        };

        $this->assertTrue($policy->before(tap(User::make()->makeSuper())->save(), 'view'));
        $this->assertTrue($policy->before(tap(User::make()->assignRole('configurer'))->save(), 'view'));
        $this->assertNull($policy->before(tap(User::make())->save(), 'view'));
    }
}
