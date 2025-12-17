<?php

namespace Statamic\Forms;

use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget as BaseWidget;

class Widget extends BaseWidget
{
    protected static $handle = 'form';

    public function component()
    {
        $form = Form::find($handle = $this->config('form'));

        if (! $form) {
            return VueComponent::render('dynamic-html-renderer', [
                'html' => "Error: Form [$handle] doesn't exist.",
            ]);
        }

        if (! User::current()->can('view', $form)) {
            return;
        }

        return VueComponent::render('form-widget', [
            'form' => $form->handle(),
            'fields' => $this->config('fields', []),
            'title' => $this->config('title', $form->title()),
            'submissionsUrl' => cp_route('forms.show', $form->handle()),
            'initialPerPage' => $this->config('limit', 5),
        ]);
    }
}
