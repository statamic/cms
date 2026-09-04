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
}
