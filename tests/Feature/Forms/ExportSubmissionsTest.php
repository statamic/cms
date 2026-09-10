<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ExportSubmissionsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['statamic.forms.forms'] = $this->fakeStacheDirectory.'/forms';
    }

    #[Test]
    public function it_exports_submissions_sorted_by_a_column()
    {
        $form = $this->createFormWithSubmissions();

        $response = $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->export($form, 'csv', ['sort' => 'name', 'order' => 'asc'])
            ->assertOk();

        $this->assertEquals(['Alice', 'Bravo', 'Charlie'], $this->namesFromCsv($response->getContent()));
    }

    #[Test]
    public function it_exports_submissions_sorted_in_the_requested_direction()
    {
        $form = $this->createFormWithSubmissions();

        $response = $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->export($form, 'csv', ['sort' => 'name', 'order' => 'desc'])
            ->assertOk();

        $this->assertEquals(['Charlie', 'Bravo', 'Alice'], $this->namesFromCsv($response->getContent()));
    }

    #[Test]
    public function it_exports_submissions_sorted_by_the_listings_default_date_column()
    {
        $form = $this->createFormWithSubmissions();

        $response = $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->export($form, 'csv', ['sort' => 'datestamp', 'order' => 'desc'])
            ->assertOk();

        $this->assertEquals(['Bravo', 'Alice', 'Charlie'], $this->namesFromCsv($response->getContent()));
    }

    private function createFormWithSubmissions()
    {
        $blueprint = Blueprint::makeFromFields(['name' => ['type' => 'text']]);
        Blueprint::partialMock()->shouldReceive('find')->with('forms.test')->andReturn($blueprint);

        $form = tap(Form::make('test'))->save();

        // A submission's date is derived from its ID, so the IDs are set explicitly
        // to keep the dates distinct and the resulting sort order deterministic.
        collect(['Charlie' => '1700000001', 'Alice' => '1700000002', 'Bravo' => '1700000003'])
            ->each(fn ($id, $name) => FormSubmission::make()->form($form)->id($id)->data(['name' => $name])->save());

        return $form;
    }

    private function export($form, $type, $params = [])
    {
        return $this->get(cp_route('forms.export', array_merge([
            'form' => $form->handle(),
            'type' => $type,
        ], $params)));
    }

    private function namesFromCsv($csv)
    {
        return collect(explode("\n", trim($csv)))
            ->slice(1)
            ->map(fn ($line) => str_getcsv($line)[0])
            ->values()
            ->all();
    }
}
