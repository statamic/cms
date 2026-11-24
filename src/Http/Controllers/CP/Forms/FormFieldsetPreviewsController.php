<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Statamic\Facades\Fieldset;
use Statamic\Fields\Field;
use Statamic\Http\Controllers\CP\CpController;

class FormFieldsetPreviewsController extends CpController
{
    public function __invoke(Request $request, $form)
    {
        $this->authorize('editFields', $form);

        $request->validate([
            'fieldset' => 'required|string',
        ]);

        $fieldset = Fieldset::find($request->fieldset);

        if (! $fieldset) {
            return ['previews' => []];
        }

        $previews = collect($fieldset->fields()->all())
            ->mapWithKeys(fn (Field $field) => [$field->handle() => $this->fieldPreview($field->config())])
            ->all();

        return ['previews' => $previews];
    }

    private function fieldPreview(array $config): ?array
    {
        try {
            $field = new Field('preview', $config);
            $field->setValue($field->defaultValue());

            return [
                'config' => $field->toPublishArray(),
                'value' => $field->fieldtype()->preProcess($field->value()),
                'meta' => $field->fieldtype()->preload(),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
