<?php

namespace Tests\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Forms\Exporters\JsonExporter;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class JsonExporterTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_exports_all_columns_by_default()
    {
        $form = $this->makeFormWithSubmission();

        $json = json_decode((new JsonExporter)->setForm($form)->setConfig([])->export(), true);

        $this->assertCount(1, $json);
        $this->assertEquals(['name', 'email', 'id', 'date'], array_keys($json[0]));
    }

    #[Test]
    public function it_exports_only_the_selected_columns()
    {
        $form = $this->makeFormWithSubmission();

        $json = json_decode((new JsonExporter)->setForm($form)->setConfig([])->setColumns(['email'])->export(), true);

        $this->assertEquals(['email', 'id'], array_keys($json[0]));
        $this->assertEquals('john@example.com', $json[0]['email']);
    }

    private function makeFormWithSubmission()
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                ['handle' => 'email', 'field' => ['type' => 'short_answer']],
            ],
        ]))->save();

        FormSubmission::make()->form($form)->data(['name' => 'John', 'email' => 'john@example.com'])->save();

        return $form;
    }
}
