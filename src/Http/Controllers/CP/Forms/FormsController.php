<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\CP\Column;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Rules\Handle;
use Statamic\Statamic;
use Statamic\Support\Str;

use function Statamic\trans as __;

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
                    'title' => __($form->title()),
                    'submissions' => $form->querySubmissions()->whereNull('partial')->count(),
                    'show_url' => $form->showUrl(),
                    'submissions_url' => $form->submissionsUrl(),
                    'edit_url' => $form->editUrl(),
                    'can_edit' => User::current()->can('edit', $form),
                    'can_edit_blueprint' => User::current()->can('configure form fields', $form),
                ];
            })
            ->values();

        return Inertia::render('forms/Index', [
            'forms' => $forms,
            'initialColumns' => $columns,
            'actionUrl' => cp_route('forms.actions.run'),
            'canCreate' => User::current()->can('create', FormContract::class) && $this->canCreateAdditionalForms(),
            'createUrl' => cp_route('forms.create'),
            'configureEmailUrl' => cp_route('utilities.email'),
        ]);
    }

    public function show($form)
    {
        if ($form->querySubmissions()->count() === 0) {
            return redirect()->route('statamic.cp.forms.builder.edit', $form->handle());
        }

        return redirect()->route('statamic.cp.forms.submissions.index', $form->handle());
    }

    public function create()
    {
        $this->authorizeProIf(! $this->canCreateAdditionalForms());

        $this->authorize('create', FormContract::class);

        return Inertia::render('forms/Create', [
            'submitUrl' => cp_route('forms.store'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeProIf(! $this->canCreateAdditionalForms());

        $this->authorize('create', FormContract::class, __('You are not authorized to create forms.'));

        $request->validate([
            'title' => 'required',
            'handle' => ['nullable', new Handle],
        ]);

        $handle = $request->handle ?? Str::snake($request->title);

        if (Form::find($handle)) {
            throw new \Exception(__('Form already exists'));
        }

        $form = tap(Form::make($handle)->title($request->title))->save();

        session()->flash('success', __('Form created'));

        return ['redirect' => $form->showUrl()];
    }

    public function edit($form)
    {
        $this->authorize('edit', $form);

        $blueprint = $this->editFormBlueprint($form);

        $values = array_merge($form->data()->all(), [
            'handle' => $form->handle(),
            'title' => __($form->title()),
            'honeypot' => $form->honeypot(),
            'store' => $form->store(),
            'email' => $form->email(),
            'generate_fake_submissions' => (bool) $form->get('generate_fake_submissions', true),
        ]);

        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        return Inertia::render('forms/Edit', [
            'form' => $form,
            'blueprint' => $blueprint->toPublishArray(),
            'initialValues' => $fields->values(),
            'initialMeta' => $fields->meta(),
            'action' => cp_route('forms.update', $form->handle()),
        ]);
    }

    public function update($form, Request $request)
    {
        $this->authorize('edit', $form);

        $fields = $this->editFormBlueprint($form)->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->all();

        $data = collect($values)->except(['title', 'honeypot', 'store', 'email']);

        $form
            ->title($values['title'])
            ->honeypot($values['honeypot'])
            ->store($values['store'])
            ->email($values['email'])
            ->merge($data);

        $form->save();

        $this->success(__('Saved'));
    }

    public function destroy($form)
    {
        $this->authorize('delete', $form, 'You are not authorized to delete this form.');

        $form->delete();
    }

    private function canCreateAdditionalForms(): bool
    {
        return Form::all()->isEmpty()
            || Statamic::pro()
            || Statamic::formsProInstalled();
    }

    protected function editFormBlueprint($form)
    {
        $fields = [
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
                    'generate_fake_submissions' => [
                        'display' => __('Enable Fake Submission Generator'),
                        'type' => 'toggle',
                        'default' => true,
                        'instructions' => __('statamic::messages.form_configure_generate_fake_submissions_instructions'),
                    ],
                ],
            ],
            'email' => [
                'display' => __('Email'),
                'fields' => [
                    'email' => [
                        'type' => 'grid',
                        'mode' => 'stacked',
                        'full_width_setting' => true,
                        'add_row' => __('Add Email'),
                        'instructions' => __('statamic::messages.form_configure_email_instructions'),
                        'fields' => [
                            [
                                'handle' => 'to',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('Recipient(s)'),
                                    'validate' => [
                                        'required',
                                    ],
                                    'instructions' => __('statamic::messages.form_configure_email_to_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'cc',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('CC Recipient(s)'),
                                    'instructions' => __('statamic::messages.form_configure_email_cc_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'bcc',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('BCC Recipient(s)'),
                                    'instructions' => __('statamic::messages.form_configure_email_bcc_instructions'),
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
                            [
                                'handle' => 'mailer',
                                'field' => [
                                    'type' => 'select',
                                    'instructions' => __('statamic::messages.form_configure_mailer_instructions'),
                                    'options' => array_keys(config('mail.mailers')),
                                    'clearable' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach (Form::extraConfigFor($form->handle()) as $handle => $config) {
            $merged = false;
            foreach ($fields as $sectionHandle => $section) {
                if ($section['display'] == __($config['display'])) {
                    $fields[$sectionHandle]['fields'] += $config['fields'];
                    $merged = true;
                }
            }

            if (! $merged) {
                $fields[$handle] = $config;
            }
        }

        return Blueprint::make()->setContents(collect([
            'tabs' => [
                'main' => [
                    'sections' => collect($fields)->map(function ($section) {
                        return [
                            'display' => $section['display'],
                            'fields' => collect($section['fields'])->map(function ($field, $handle) {
                                return [
                                    'handle' => $handle,
                                    'field' => $field,
                                ];
                            })->values()->all(),
                        ];
                    })->values()->all(),
                ],
            ],
        ])->all());

    }
}
