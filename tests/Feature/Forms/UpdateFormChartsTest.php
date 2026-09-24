<?php

namespace Tests\Feature\Forms;

use Facades\Statamic\Console\Processes\Composer;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateFormChartsTest extends TestCase
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
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), ['charts' => []])
            ->assertNotFound();
    }

    #[Test]
    public function it_denies_access_without_permission()
    {
        $this->setTestRoles(['test' => ['access cp', 'view form submissions']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = $this->makeForm();

        $this
            ->from('/original')
            ->actingAs($user)
            ->patch(cp_route('forms.submissions.charts.update', $form->handle()), ['charts' => []])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_saves_the_chart_layout()
    {
        $form = $this->makeForm();

        $charts = [
            ['field' => 'rating', 'chart' => 'horizontal_bar'],
            ['field' => 'color', 'chart' => 'pie'],
        ];

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), ['charts' => $charts])
            ->assertNoContent();

        $this->assertEquals($charts, Form::find('survey')->charts());
    }

    #[Test]
    public function it_saves_an_empty_layout()
    {
        $form = $this->makeForm();
        $form->charts([['field' => 'color', 'chart' => 'pie']])->save();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), ['charts' => []])
            ->assertNoContent();

        $this->assertEquals([], Form::find('survey')->charts());
    }

    #[Test]
    public function it_rejects_fields_that_dont_exist_on_the_form()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), [
                'charts' => [['field' => 'missing', 'chart' => 'pie']],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('charts');

        $this->assertNull(Form::find('survey')->charts());
    }

    #[Test]
    public function it_rejects_hidden_fields()
    {
        $form = tap(Form::make('survey')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'color', 'field' => ['type' => 'multi_choice', 'options' => ['red' => 'Red'], 'hidden' => true]],
                    ],
                ],
            ],
        ]))->save();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), [
                'charts' => [['field' => 'color', 'chart' => 'pie']],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('charts');

        $this->assertNull(Form::find('survey')->charts());
    }

    #[Test]
    public function it_rejects_duplicate_fields()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), [
                'charts' => [
                    ['field' => 'color', 'chart' => 'pie'],
                    ['field' => 'color', 'chart' => 'horizontal_bar'],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('charts.0.field');

        $this->assertNull(Form::find('survey')->charts());
    }

    #[Test]
    public function it_allows_a_non_default_chart_that_applies_to_the_field()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), [
                'charts' => [['field' => 'rating', 'chart' => 'pie']],
            ])
            ->assertNoContent();

        $this->assertEquals([['field' => 'rating', 'chart' => 'pie']], Form::find('survey')->charts());
    }

    #[Test]
    public function it_rejects_charts_that_dont_apply_to_the_field()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), [
                'charts' => [['field' => 'color', 'chart' => 'ranked_options']],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['charts' => 'Chart ranked_options cannot be used for field color.']);

        $this->assertNull(Form::find('survey')->charts());
    }

    #[Test]
    public function it_rejects_unknown_charts()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), [
                'charts' => [['field' => 'color', 'chart' => 'line']],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('charts');

        $this->assertNull(Form::find('survey')->charts());
    }

    #[Test]
    public function it_saves_insights()
    {
        $form = $this->makeForm();

        $charts = [
            ['field' => 'rating', 'chart' => 'horizontal_bar', 'insights' => [['type' => 'star_rating']]],
            ['field' => 'color', 'chart' => 'pie', 'insights' => []],
        ];

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), ['charts' => $charts])
            ->assertNoContent();

        $this->assertSame($charts, Form::find('survey')->charts());
    }

    #[Test]
    public function it_drops_extra_keys_on_insights()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), [
                'charts' => [['field' => 'rating', 'chart' => 'horizontal_bar', 'insights' => [['type' => 'star_rating', 'extra' => 'value']]]],
            ])
            ->assertNoContent();

        $this->assertSame(
            [['field' => 'rating', 'chart' => 'horizontal_bar', 'insights' => [['type' => 'star_rating']]]],
            Form::find('survey')->charts()
        );
    }

    #[Test]
    public function it_doesnt_store_insights_when_none_are_given()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), [
                'charts' => [
                    ['field' => 'rating', 'chart' => 'horizontal_bar'],
                    ['field' => 'color', 'chart' => 'pie', 'insights' => null],
                ],
            ])
            ->assertNoContent();

        $this->assertSame(
            [['field' => 'rating', 'chart' => 'horizontal_bar'], ['field' => 'color', 'chart' => 'pie']],
            Form::find('survey')->charts()
        );
    }

    #[Test]
    public function it_rejects_unknown_insights()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), [
                'charts' => [['field' => 'rating', 'chart' => 'horizontal_bar', 'insights' => [['type' => 'missing']]]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['charts' => 'Insight missing does not exist.']);

        $this->assertNull(Form::find('survey')->charts());
    }

    #[Test]
    public function it_rejects_duplicate_insights()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), [
                'charts' => [['field' => 'rating', 'chart' => 'horizontal_bar', 'insights' => [['type' => 'star_rating'], ['type' => 'star_rating']]]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['charts' => 'Insight star_rating is used more than once for field rating.']);

        $this->assertNull(Form::find('survey')->charts());
    }

    #[Test]
    public function it_allows_the_same_insight_on_different_charts()
    {
        $form = tap(Form::make('survey')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'price', 'field' => ['type' => 'number']],
                        ['handle' => 'age', 'field' => ['type' => 'number']],
                    ],
                ],
            ],
        ]))->save();

        $charts = [
            ['field' => 'price', 'chart' => 'vertical_bar', 'insights' => [['type' => 'average']]],
            ['field' => 'age', 'chart' => 'vertical_bar', 'insights' => [['type' => 'average']]],
        ];

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), ['charts' => $charts])
            ->assertNoContent();

        $this->assertSame($charts, Form::find('survey')->charts());
    }

    #[Test]
    public function it_rejects_insights_that_dont_apply_to_the_field()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), [
                'charts' => [['field' => 'rating', 'chart' => 'horizontal_bar', 'insights' => [['type' => 'checked']]]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['charts' => 'Insight checked cannot be used for field rating.']);

        $this->assertNull(Form::find('survey')->charts());
    }

    #[Test]
    public function it_validates_the_shape_of_insights()
    {
        $form = $this->makeForm();

        $this
            ->actingAs($this->userWithPermission())
            ->patchJson(cp_route('forms.submissions.charts.update', $form->handle()), [
                'charts' => [['field' => 'rating', 'chart' => 'horizontal_bar', 'insights' => ['star_rating', ['name' => 'star_rating']]]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['charts.0.insights.0', 'charts.0.insights.1.type']);

        $this->assertNull(Form::find('survey')->charts());
    }

    private function makeForm()
    {
        return tap(Form::make('survey')->formFields([
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'color', 'field' => ['type' => 'multi_choice', 'options' => ['red' => 'Red', 'blue' => 'Blue']]],
                        ['handle' => 'rating', 'field' => ['type' => 'star_rating']],
                    ],
                ],
            ],
        ]))->save();
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);

        return tap(User::make()->assignRole('test'))->save();
    }
}
