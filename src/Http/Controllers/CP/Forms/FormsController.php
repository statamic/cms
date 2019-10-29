<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\Facades\Form;
use Statamic\Facades\User;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Forms\Form as FormContract;

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
                    'deleteable' => User::current()->can('delete', $form)
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
                'label' => $params['label']
            ];
            unset($params['type'], $params['label']);

            foreach ($params as $key => $value) {
                $metric['params'][] = [
                    'value' => $key,
                    'text' => $value
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
            'redirect' => $form->showUrl()
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
     * Clean up the email values from the Grid field
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
        return Blueprint::makeFromFields([
            'title' => [
                'type' => 'text',
                'validate' => 'required',
                'width' => 50,
                'instructions' => __('statamic::messages.forms_title_instructions'),
            ],
            'handle' => [
                'type' => 'text',
                'validate' => 'required|alpha_dash',
                'width' => 50,
                'instructions' => 'How you\'ll reference to this form in your templates. Cannot easily be changed.'
            ],
            'fields' => ['type' => 'section'],
            'blueprint' => [
                'type' => 'blueprints',
                'instructions' => __('statamic::messages.forms_blueprint_instructions'),
                'validate' => 'min:1',
                'max_items' => 1,
                'width' => 50,
            ],
            'honeypot' => [
                'type' => 'text',
                'width' => 50,
                'instructions' => __('statamic::messages.forms_honeypot_instructions'),
            ],
            'submissions' => ['type' => 'section'],
            'store' => [
                'display' => __('Store Submissions'),
                'type' => 'toggle',
                'width' => 100,
                'instructions' => __('statamic::messages.forms_store_instructions'),
            ],
            'email' => [
                'type' => 'grid',
                'mode' => 'stacked',
                'instructions' => __('statamic::messages.forms_email_instructions'),
                'fields' => [
                    [
                        'handle' => 'to',
                        'field' => [
                            'type' => 'text',
                            'display' => __('Recipient (To)'),
                            'width' => 50,
                            'validate' => [
                                'required',
                            ],
                            'instructions' => __('statamic::messages.forms_email_to_instructions'),
                        ]
                    ],
                    [
                        'handle' => 'from',
                        'field' => [
                            'type' => 'text',
                            'display' => __('Sender (From)'),
                            'width' => 50,
                            'instructions' => __('statamic::messages.forms_email_from_instructions'),
                        ]
                    ],
                    [
                        'handle' => 'reply_to',
                        'field' => [
                            'type' => 'text',
                            'display' => __('Reply To'),
                            'width' => 50,
                            'instructions' => __('statamic::messages.forms_email_reply_to_instructions'),
                        ]
                    ],
                    [
                        'handle' => 'subject',
                        'field' => [
                            'type' => 'text',
                            'width' => 100,
                            'instructions' => __('statamic::messages.forms_email_subject_instructions'),
                        ]
                    ],
                    [
                        'handle' => 'template',
                        'field' => [
                            'type' => 'text',
                            'width' => 100,
                            'instructions' => __('statamic::messages.forms_email_template_instructions'),
                        ]
                    ],
                ]
            ],




            // metrics
        ]);
    }
}
