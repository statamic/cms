<?php

namespace Statamic\Forms;

use Statamic\API\Str;
use Statamic\API\Form;
use Statamic\API\File;
use Statamic\API\Metrics;
use Statamic\Widgets\Widget as BaseWidget;

class Widget extends BaseWidget
{
    protected static $handle = 'form';

    public function html()
    {
        $form = $this->get('form');

        if (! Form::get($form)) {
            return "Error: Form [$form] doesn't exist.";
        }

        $form = Form::get($form);

        $data = [
            'form'        => $form,
            'format'      => $this->get('date_format', $form->dateFormat()),
            'fields'      => $this->get('fields', []),
            'submissions' => collect_content($form->submissions())->reverse()->limit($this->getInt('limit', 5))->toArray(),
            'title'       => $this->get('title', $form->title())
        ];

        return view('statamic::forms.widget', $data);
    }
}
