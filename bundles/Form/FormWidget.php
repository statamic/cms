<?php

namespace Statamic\Addons\Form;

use Statamic\API\Str;
use Statamic\API\Form;
use Statamic\API\File;
use Statamic\API\Metrics;
use Statamic\Extend\Widget;

class FormWidget extends Widget
{
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

        return $this->view('widget', $data);
    }
}
