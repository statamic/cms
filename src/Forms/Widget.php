<?php

namespace Statamic\Forms;

use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget as BaseWidget;

use function Statamic\trans as __;

class Widget extends BaseWidget
{
    protected static $handle = 'form';

    public static function description(): ?string
    {
        return __('statamic::messages.widget_form_description');
    }

    public static function icon(): string
    {
        return 'forms';
    }

    public function blueprint(): \Statamic\Fields\Blueprint
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'form',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Form'),
                                        'instructions' => __('statamic::messages.widget_form_form_instructions'),
                                        'validate' => 'required',
                                    ],
                                ],
                                [
                                    'handle' => 'title',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Title'),
                                    ],
                                ],
                                [
                                    'handle' => 'limit',
                                    'field' => [
                                        'type' => 'integer',
                                        'display' => __('Limit'),
                                        'default' => 5,
                                    ],
                                ],
                                [
                                    'handle' => 'show_table_header',
                                    'field' => [
                                        'type' => 'toggle',
                                        'display' => __('Show Table Header'),
                                        'default' => false,
                                    ],
                                ],
                                [
                                    'handle' => 'fields',
                                    'field' => [
                                        'type' => 'list',
                                        'display' => __('Fields'),
                                    ],
                                ],
                                ...$this->commonBlueprintFields(),
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

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
            'showTableHeader' => $this->config('show_table_header', false),
            'submissionsUrl' => cp_route('forms.show', $form->handle()),
            'initialPerPage' => $this->config('limit', 5),
        ]);
    }
}
