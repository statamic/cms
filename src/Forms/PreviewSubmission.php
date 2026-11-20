<?php

namespace Statamic\Forms;

use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Fields\Field;

class PreviewSubmission
{
    public function values(FormContract $form): array
    {
        return $form->blueprint()->fields()->all()
            ->mapWithKeys(fn (Field $field) => [$field->handle() => $field->fieldtype()->previewValue()])
            ->all();
    }
}
