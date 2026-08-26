<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class FormSummaryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturnTrue()->byDefault();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_requires_forms_pro()
    {
        Composer::shouldReceive('isInstalled')->with('statamic/forms-pro')->andReturnFalse();

        $form = $this->makeForm();

        $this
            ->actingAs($this->superUser())
            ->getJson(cp_route('forms.submissions.summary', $form->handle()))
            ->assertNotFound();
    }

    #[Test]
    public function it_shows_the_summary_with_the_view_form_submissions_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = $this->makeForm();

        $this
            ->actingAs($user)
            ->getJson(cp_route('forms.submissions.summary', $form->handle()))
            ->assertOk();
    }

    #[Test]
    public function it_denies_access_without_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = $this->makeForm();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get(cp_route('forms.submissions.summary', $form->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_summarizes_fields_with_a_default_chart_in_blueprint_order()
    {
        $form = $this->makeForm();

        $this->submit($form, ['name' => 'Alice', 'color' => 'red', 'rating' => 5]);
        $this->submit($form, ['name' => 'Bob', 'color' => 'red', 'rating' => 4]);
        $this->submit($form, ['name' => 'Carol', 'color' => 'blue']);

        $response = $this
            ->actingAs($this->superUser())
            ->getJson(cp_route('forms.submissions.summary', $form->handle()))
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('fields.0.handle', 'name')
            ->assertJsonPath('fields.1.handle', 'color')
            ->assertJsonPath('fields.1.fieldtype', 'multi_choice')
            ->assertJsonPath('fields.1.responses', 3)
            ->assertJsonPath('fields.1.chart.handle', 'pie')
            ->assertJsonPath('fields.1.chart.component', 'ui-pie-chart')
            ->assertJsonPath('fields.2.handle', 'rating')
            ->assertJsonPath('fields.2.responses', 2)
            ->assertJsonPath('fields.2.insights.0.handle', 'star_rating')
            ->assertJsonPath('fields.2.insights.0.component', 'star-rating-insight')
            ->assertJsonPath('fields.2.insights.0.props.average', 4.5)
            ->assertJsonPath('fields.2.insights.0.props.total', 5);

        $this->assertEquals([
            ['key' => 'red', 'label' => 'Red', 'count' => 2, 'percent' => 67],
            ['key' => 'blue', 'label' => 'Blue', 'count' => 1, 'percent' => 33],
        ], $response->json('fields.1.chart.props.items'));

        $this->assertCount(3, $response->json('fields'));
    }

    #[Test]
    public function it_ignores_fields_that_cant_be_charted()
    {
        $form = tap(Form::make('survey')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'intro', 'field' => ['type' => 'heading', 'display' => 'Tell us about yourself']],
                        ['handle' => 'joined', 'field' => ['type' => 'date_picker', 'display' => 'When did you join?']],
                        ['handle' => 'color', 'field' => ['type' => 'multi_choice', 'options' => ['red' => 'Red']]],
                    ],
                ],
            ],
        ]))->save();

        $this
            ->actingAs($this->superUser())
            ->getJson(cp_route('forms.submissions.summary', $form->handle()))
            ->assertOk()
            ->assertJsonCount(1, 'fields')
            ->assertJsonPath('fields.0.handle', 'color')
            ->assertJsonCount(1, 'meta.fields')
            ->assertJsonPath('meta.fields.0.handle', 'color');
    }

    #[Test]
    public function it_uses_the_saved_chart_layout()
    {
        $form = $this->makeForm();
        $form->charts([['field' => 'rating', 'chart' => 'horizontal_bar']])->save();

        $this
            ->actingAs($this->superUser())
            ->getJson(cp_route('forms.submissions.summary', $form->handle()))
            ->assertOk()
            ->assertJsonCount(1, 'fields')
            ->assertJsonPath('fields.0.handle', 'rating')
            ->assertJsonPath('fields.0.chart.handle', 'horizontal_bar');
    }

    #[Test]
    public function it_scopes_counts_to_the_search_query()
    {
        $form = $this->makeForm();

        $this->submit($form, ['name' => 'Alice', 'color' => 'red']);
        $this->submit($form, ['name' => 'Bob', 'color' => 'blue']);

        $this
            ->actingAs($this->superUser())
            ->getJson(cp_route('forms.submissions.summary', $form->handle()).'?search=alice')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('fields.1.chart.props.items.0.count', 1)
            ->assertJsonPath('fields.1.chart.props.items.1.count', 0);
    }

    #[Test]
    public function it_only_includes_meta_for_users_who_can_edit_the_form()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->superUser())
            ->getJson(cp_route('forms.submissions.summary', $form->handle()))
            ->assertJsonCount(5, 'meta.charts')
            ->assertJsonPath('meta.charts.0.handle', 'horizontal_bar')
            ->assertJsonPath('meta.charts.0.component', 'ui-horizontal-bar-chart')
            ->assertJsonPath('meta.fields.0.handle', 'name')
            ->assertJsonPath('meta.fields.1.handle', 'color')
            ->assertJsonPath('meta.fields.1.default_chart', 'pie')
            ->assertJsonPath('meta.fields.2.handle', 'rating');

        $this->setTestRoles(['test' => ['access cp', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->getJson(cp_route('forms.submissions.summary', $form->handle()))
            ->assertJsonPath('meta', []);
    }

    private function makeForm()
    {
        return tap(Form::make('survey')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                        ['handle' => 'color', 'field' => ['type' => 'multi_choice', 'options' => ['red' => 'Red', 'blue' => 'Blue']]],
                        ['handle' => 'rating', 'field' => ['type' => 'star_rating']],
                    ],
                ],
            ],
        ]))->save();
    }

    private function submit($form, array $data)
    {
        FormSubmission::make()->form($form)->data($data)->save();
    }

    private function superUser()
    {
        return tap(User::make()->makeSuper())->save();
    }
}
