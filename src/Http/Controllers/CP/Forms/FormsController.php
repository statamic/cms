<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\CP\Column;
use Statamic\Facades\Action;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Str;

class FormsController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('index', FormContract::class);

        $columns = [
            Column::make('title')->label(__('Title')),
            Column::make('submissions')->label(__('Submissions')),
        ];

        $forms = Form::all()
            ->filter(function ($form) {
                return User::current()->can('view', $form);
            })
            ->map(function ($form) {
                return [
                    'id' => $form->handle(),
                    'title' => $form->title(),
                    'submissions' => $form->submissions()->count(),
                    'show_url' => $form->showUrl(),
                    'edit_url' => $form->editUrl(),
                    'blueprint_url' => cp_route('forms.blueprint.edit', $form->handle()),
                    'actions' => Action::for($form),
                ];
            })
            ->values();

        if ($request->wantsJson()) {
            return [
                'meta' => [
                    'columns' => $columns,
                    'activeFilterBadges' => [],
                ],
                'data' => $forms,
            ];
        }

        return view('statamic::forms.index', [
            'forms' => $forms,
            'initialColumns' => $columns,
            'actionUrl' => cp_route('forms.actions.run'),
        ]);
    }

    public function show($form)
    {
        $this->authorize('view', $form);

        $columns = $form
            ->blueprint()
            ->columns()
            ->prepend(Column::make('datestamp'), 'datestamp')
            ->setPreferred("forms.{$form->handle()}.columns")
            ->rejectUnlisted()
            ->values();

        return view('statamic::forms.show', compact('form', 'columns'));
    }

    /**
     * Get the metrics array ready to be injected into a Grid field.
     *
     * @param  Form  $form
     * @return array
     */
    private function preProcessMetrics($form)
    {
        $metrics = [];

        foreach ($form->formset()->get('metrics', []) as $params) {
            $metric = [
                'type' => $params['type'],
                'label' => $params['label'],
            ];
            unset($params['type'], $params['label']);

            foreach ($params as $key => $value) {
                $metric['params'][] = [
                    'value' => $key,
                    'text' => $value,
                ];
            }

            $metrics[] = $metric;
        }

        return $metrics;
    }

    public function create()
    {
        $this->authorizeProIf(Form::all()->count() >= 1);

        $this->authorize('create', FormContract::class);

        return view('statamic::forms.create');
    }

    public function store(Request $request)
    {
        $this->authorizeProIf(Form::all()->count() >= 1);

        $this->authorize('create', FormContract::class, __('You are not authorized to create forms.'));

        $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
        ]);

        $handle = $request->handle ?? Str::snake($request->title);

        if (Form::find($handle)) {
            throw new \Exception(__('Form already exists'));
        }

        $form = tap(Form::make($handle)->title($request->title))->save();

        session()->flash('success', __('Form created'));

        return ['redirect' => $form->editUrl()];
    }

    public function edit($form)
    {
        $this->authorize('edit', $form);

        $values = [
            'handle' => $form->handle(),
            'title' => $form->title(),
            'honeypot' => $form->honeypot(),
            'store' => $form->store(),
            'email' => $form->email(),
        ];

        $fields = ($blueprint = $this->editFormBlueprint($form))
            ->fields()
            ->addValues($values)
            ->preProcess();

        return view('statamic::forms.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'form' => $form,
        ]);
    }

    public function update($form, Request $request)
    {
        $this->authorize('edit', $form);

        $fields = $this->editFormBlueprint($form)->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->all();

        $form
            ->title($values['title'])
            ->honeypot($values['honeypot'])
            ->store($values['store'])
            ->email($values['email']);

        $form->save();

        $this->success(__('Saved'));
    }

    public function destroy($form)
    {
        $this->authorize('delete', $form, 'You are not authorized to delete this form.');

        $form->delete();
    }

    protected function editFormBlueprint($form)
    {
        return Blueprint::makeFromSections([
            'name' => [
                'display' => __('Name'),
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'validate' => 'required',
                        'instructions' => __('statamic::messages.form_configure_title_instructions'),
                    ],
                ],
            ],
            'fields' => [
                'display' => __('Fields'),
                'fields' => [
                    'blueprint' => [
                        'type' => 'html',
                        'instructions' => __('statamic::messages.form_configure_blueprint_instructions'),
                        'html' => ''.
                            '<div class="text-xs">'.
                            '   <a href="'.cp_route('forms.blueprint.edit', $form->handle()).'" class="text-blue">'.__('Edit').'</a>'.
                            '</div>',
                    ],
                    'honeypot' => [
                        'type' => 'text',
                        'instructions' => __('statamic::messages.form_configure_honeypot_instructions'),
                    ],
                ],
            ],
            'submissions' => [
                'display' => __('Submissions'),
                'fields' => [
                    'store' => [
                        'display' => __('Store Submissions'),
                        'type' => 'toggle',
                        'instructions' => __('statamic::messages.form_configure_store_instructions'),
                    ],
                ],
            ],
            'email' => [
                'display' => __('Email'),
                'fields' => [
                    'email' => [
                        'type' => 'grid',
                        'mode' => 'stacked',
                        'add_row' => __('Add Email'),
                        'instructions' => __('statamic::messages.form_configure_email_instructions'),
                        'fields' => [
                            [
                                'handle' => 'to',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('Recipient'),
                                    'validate' => [
                                        'required',
                                    ],
                                    'instructions' => __('statamic::messages.form_configure_email_to_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'from',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('Sender'),
                                    'instructions' => __('statamic::messages.form_configure_email_from_instructions').' ('.config('mail.from.address').').',
                                ],
                            ],
                            [
                                'handle' => 'reply_to',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('Reply To'),
                                    'instructions' => __('statamic::messages.form_configure_email_reply_to_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'subject',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('Subject'),
                                    'instructions' => __('statamic::messages.form_configure_email_subject_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'html',
                                'field' => [
                                    'type' => 'template',
                                    'display' => __('HTML view'),
                                    'instructions' => __('statamic::messages.form_configure_email_html_instructions'),
                                    'folder' => config('statamic.forms.email_view_folder'),
                                ],
                            ],
                            [
                                'handle' => 'text',
                                'field' => [
                                    'type' => 'template',
                                    'display' => __('Text view'),
                                    'instructions' => __('statamic::messages.form_configure_email_text_instructions'),
                                    'folder' => config('statamic.forms.email_view_folder'),
                                ],
                            ],
                            [
                                'handle' => 'markdown',
                                'field' => [
                                    'type' => 'toggle',
                                    'display' => __('Markdown'),
                                    'instructions' => __('statamic::messages.form_configure_email_markdown_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'attachments',
                                'field' => [
                                    'type' => 'toggle',
                                    'display' => __('Attachments'),
                                    'instructions' => __('statamic::messages.form_configure_email_attachments_instructions'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // metrics
        ]);
    }
}
