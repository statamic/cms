<?php

namespace Statamic\Http\Controllers\CP\Forms;

trait ExtractsFromSubmissionFields
{
    protected function extractFromFields($submission, $blueprint)
    {
        $values = $submission->data();

        $fields = $blueprint
            ->fields()
            ->addValues($values->all())
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }
}
