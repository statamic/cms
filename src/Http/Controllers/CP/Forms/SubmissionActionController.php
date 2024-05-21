<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\Facades\Action;
use Statamic\Http\Controllers\CP\ActionController;

class SubmissionActionController extends ActionController
{
    use ExtractsFromSubmissionFields;

    protected function getSelectedItems($items, $context)
    {
        $form = $this->request->route('form');

        return $items->map(function ($item) use ($form) {
            return $form->submission($item);
        });
    }

    protected function getItemData($submission, $context): array
    {
        $blueprint = $submission->blueprint();

        [$values] = $this->extractFromFields($submission, $blueprint);

        return [
            'id' => $submission->id(),
            'values' => array_merge($values, ['id' => $submission->id()]),
            'itemActions' => Action::for($submission, $context),
        ];
    }
}
