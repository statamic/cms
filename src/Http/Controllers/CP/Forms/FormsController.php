<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class FormsController extends CpController
{
    public function index()
    {
        $this->authorize('index', FormContract::class);

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
                    'delete_url' => $form->deleteUrl(),
                    'deleteable' => User::current()->can('delete', $form),
                ];
            })
            ->values();

        return view('statamic::forms.index', compact('forms'));
    }

    public function show($form)
    {
        $this->authorize('view', $form);

        return view('statamic::forms.show', compact('form'));
    }

    /**
     * Get the metrics array ready to be injected into a Grid field.
     *
     * @param  Form $form
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
        $this->authorize('create', FormContract::class);

        return view('statamic::forms.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', FormContract::class);

        $data = $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
            'blueprint' => 'nullable|array',
            'store' => 'nullable|boolean',
            'email' => 'nullable|string',
        ]);

        $handle = $request->handle ?? snake_case($request->title);

        $form = $this->hydrateForm(Form::make($handle), $data);
        $form->save();

        $this->success(__('Created'));

        return [
            'success' => true,
            'redirect' => $form->showUrl(),
        ];
    }

    public function edit($form)
    {
        $this->authorize('edit', $form);

        $values = $form->toArray();

        $fields = ($blueprint = $this->editFormBlueprint())
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

        $data = $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
            'blueprint' => 'nullable|array',
            'honeypot' => 'nullable|string',
            'store' => 'nullable|boolean',
            'email' => 'nullable|array',
            'email.*.to' => 'required|email',
            'email.*.from' => 'nullable|email',
            'email.*.reply_to' => 'nullable|email',
        ]);

        $this->hydrateForm($form, $data);
        $form->save();

        $this->success(__('Saved'));

        return $form->toArray();
    }

    protected function hydrateForm($form, $data)
    {
        if (is_string($data['email'])) {
            $data['email'] = [['to' => $data['email']]];
        }

        return $form
            ->title($data['title'])
            ->handle($data['handle'])
            ->blueprint(collect($data['blueprint'])->first())
            ->honeypot($data['honeypot'] ?? null)
            ->store($data['store'] ?? null)
            ->email($data['email'] ?? null);
    }

    /**
     * Clean up the email values from the Grid field.
     *
     * @return array
     */
    private function prepareEmail()
    {
        $emails = [];

        foreach ($this->request->input('formset.email') as $email) {
            $emails[] = array_except(array_filter($email), '_id');
        }

        return $emails;
    }

    public function destroy($form)
    {
        $this->authorize('delete', $form, 'You are not authorized to delete this form.');

        $form->delete();
    }

    protected function editFormBlueprint()
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
                    'handle' => [
                        'type' => 'text',
                        'validate' => 'required|alpha_dash',
                        'instructions' => __('statamic::messages.form_configure_handle_instructions'),
                    ],
                ],
            ],
            'fields' => [
                'display' => __('Fields'),
                'fields' => [
                    'blueprint' => [
                        'type' => 'blueprints',
                        'instructions' => __('statamic::messages.form_configure_blueprint_instructions'),
                        'validate' => 'min:1',
                        'max_items' => 1,
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
                        'add_row' => 'Add Email',
                        'instructions' => __('statamic::messages.form_configure_email_instructions'),
                        'fields' => [
                            [
                                'handle' => 'to',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('Recipient (To)'),
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
                                    'display' => __('Sender (From)'),
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
                                    'instructions' => __('statamic::messages.form_configure_email_subject_instructions'),
                                ],
                            ],
                            [
                                'handle' => 'template',
                                'field' => [
                                    'type' => 'template',
                                    'instructions' => __('statamic::messages.form_configure_email_template_instructions'),
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
