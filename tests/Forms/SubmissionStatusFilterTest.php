<?php

namespace Tests\Forms;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Query\Scopes\Filters\SubmissionStatus;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SubmissionStatusFilterTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    #[DataProvider('statusProvider')]
    public function it_filters_submissions_by_status(string $status, array $expected)
    {
        $form = tap(Form::make('test'))->save();

        FormSubmission::make()->form($form)->data(['id' => 'finalized'])->save();
        FormSubmission::make()->form($form)->asPartial()->data(['id' => 'partial'])->save();
        FormSubmission::make()->form($form)->markAsSpam()->data(['id' => 'flagged-spam'])->save();
        FormSubmission::make()->form($form)->asPartial()->markAsSpam()->data(['id' => 'unfinalized-spam'])->save();

        $query = FormSubmission::query()->where('form', 'test');

        (new SubmissionStatus)->apply($query, ['status' => $status]);

        $this->assertEquals($expected, $query->get()->map->get('id')->sort()->values()->all());
    }

    public static function statusProvider(): array
    {
        return [
            'finalized' => ['finalized', ['finalized']],
            'partial' => ['partial', ['partial']],
            'spam' => ['spam', ['flagged-spam', 'unfinalized-spam']],
        ];
    }
}
