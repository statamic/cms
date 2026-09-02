<?php

namespace Tests\Forms;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Forms\Exporters\CsvExporter;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CsvExporterTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_neutralizes_formula_injection_in_submission_values()
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'short_answer']],
            ],
        ]))->save();

        FormSubmission::make()->form($form)->data(['name' => '=1+1'])->save();

        $csv = (new CsvExporter)->setForm($form)->setConfig([])->export();

        $this->assertStringContainsString('\'=1+1', $csv);
        $this->assertStringNotContainsString('"=1+1', $csv);
    }

    #[Test]
    public function it_exports_all_columns_by_default()
    {
        $form = $this->makeFormWithSubmission();

        $csv = (new CsvExporter)->setForm($form)->setConfig([])->export();

        $this->assertStringStartsWith("name,email,date\n", $csv);
        $this->assertStringContainsString('John,john@example.com,', $csv);
    }

    #[Test]
    public function it_exports_only_the_selected_columns_in_form_order()
    {
        $form = $this->makeFormWithSubmission();

        $csv = (new CsvExporter)->setForm($form)->setConfig([])->setColumns(['date', 'name', 'unknown'])->export();

        $this->assertStringStartsWith("name,date\n", $csv);
        $this->assertStringNotContainsString('john@example.com', $csv);
    }

    #[Test]
    public function it_uses_display_names_for_selected_columns()
    {
        $form = $this->makeFormWithSubmission();

        $csv = (new CsvExporter)->setForm($form)->setConfig(['headers' => 'display'])->setColumns(['email', 'date'])->export();

        $this->assertStringStartsWith("\"Email Address\",Date\n", $csv);
    }

    private function makeFormWithSubmission()
    {
        $form = tap(Form::make('test')->formFields([
            'fields' => [
                ['handle' => 'name', 'field' => ['type' => 'short_answer']],
                ['handle' => 'email', 'field' => ['type' => 'short_answer', 'display' => 'Email Address']],
            ],
        ]))->save();

        FormSubmission::make()->form($form)->data(['name' => 'John', 'email' => 'john@example.com'])->save();

        return $form;
    }
}
