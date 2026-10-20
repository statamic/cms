<?php

namespace Tests\Feature\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Statamic\Forms\Connectors\Connector;
use Statamic\Support\VueComponent;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ConnectorEditorTest extends TestCase
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

        app('statamic.form-connectors')->put('grid_test', GridTestConnector::class);
    }

    #[Test]
    public function it_builds_the_editor_from_the_posted_connections_rather_than_the_forms()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test')->connections([
            'grid_test' => [['id' => 'global', 'rows' => [['id' => 'form-row', 'label' => 'Form']]]],
        ]))->save();

        $value = (new Field('connections', ['type' => 'form_connections', 'form' => 'test']))->fieldtype()->preProcess([
            'grid_test' => [
                ['id' => 'global', 'rows' => [['id' => 'override-row', 'label' => 'Override']]],
                ['id' => 'entry', 'rows' => [['id' => 'entry-row-1', 'label' => 'One'], ['id' => 'entry-row-2', 'label' => 'Two']]],
            ],
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.editor', [$form->handle(), 'grid_test']), $value['grid_test'])
            ->assertOk()
            ->assertJsonPath('component.name', 'grid-test-connector');

        $this->assertEquals(['global', 'entry'], collect($response->json('value'))->pluck('id')->all());
        $this->assertEquals(['global', 'entry'], array_keys($response->json('component.props.meta')));

        foreach ($response->json('value') as $connection) {
            $this->assertEquals(
                collect($connection['rows'])->pluck('_id')->all(),
                array_keys($response->json("component.props.meta.{$connection['id']}.rows.existing"))
            );
        }

        $this->assertEquals(['override-row'], collect($response->json('value.0.rows'))->pluck('_id')->all());
        $this->assertEquals(
            [['id' => 'global', 'rows' => [['id' => 'form-row', 'label' => 'Form']]]],
            Form::find('test')->connections()->get('grid_test')
        );
    }

    #[Test]
    public function it_404s_if_the_connector_doesnt_exist()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit forms']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.editor', [$form->handle(), 'unknown']), [])
            ->assertNotFound();
    }

    #[Test]
    public function it_denies_access_if_you_cant_edit_the_form()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $form = tap(Form::make('test'))->save();

        $this
            ->actingAs($user)
            ->postJson(cp_route('forms.connect.editor', [$form->handle(), 'grid_test']), [])
            ->assertForbidden();
    }
}

class GridTestConnector extends Connector
{
    protected function job(Submission $submission, array $connection): ?object
    {
        return null;
    }

    public function render(): VueComponent
    {
        return VueComponent::render('grid-test-connector', $this->blueprintProps());
    }

    public function blueprint(): \Statamic\Fields\Blueprint
    {
        return Blueprint::make()->setContents(['tabs' => ['main' => ['sections' => [['fields' => [
            ['handle' => 'rows', 'field' => ['type' => 'grid', 'fields' => [['handle' => 'label', 'field' => ['type' => 'text']]]]],
        ]]]]]]);
    }
}
