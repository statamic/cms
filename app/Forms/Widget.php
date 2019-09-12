<?php

namespace Statamic\Forms;

use Statamic\Facades\Form;
use Statamic\Widgets\Widget as BaseWidget;

class Widget extends BaseWidget
{
    protected static $handle = 'form';

    public function html()
    {
        $form = Form::find($handle = $this->config('form'));

        if (! $form) {
            return "Error: Form [$handle] doesn't exist.";
        }

        return view('statamic::forms.widget', [
            'form'        => $form,
            'format'      => $this->config('date_format', $form->dateFormat()),
            'fields'      => $this->config('fields', []),
            'submissions' => collect_content($form->submissions())->reverse()->limit((int) $this->config('limit', 5))->toArray(),
            'title'       => $this->config('title', $form->title())
        ]);
    }
}
