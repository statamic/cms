<?php

namespace Statamic\Http\Controllers\CP\Forms\Concerns;

use Statamic\Contracts\Forms\Form;

use function Statamic\trans as __;

trait ProvidesFormProps
{
    private function formProps(Form $form): array
    {
        return [
            'title' => __($form->title()),
            'handle' => $form->handle(),
            'status' => $form->status(),
        ];
    }
}
