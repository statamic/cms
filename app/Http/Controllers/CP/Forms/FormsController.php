<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\API\Str;
use Statamic\API\Form;
use Statamic\API\User;
use Statamic\CP\Column;
use Illuminate\Http\Request;
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
                    'deleteable' => me()->can('delete', $form)
                ];
            })
            ->values();

        return view('statamic::forms.index', compact('forms'));
    }

    public function show($form)
    {
        abort_unless($form = Form::find($form), 404);

        $this->authorize('view', $form);

        return view('statamic::forms.show', compact('form'));
    }

    public function getFormsetJson($form)
    {
        $array = $form->toArray();

        $array['honeypot'] = $form->honeypot();
        $array['columns'] = $form->columns()->map->field();
        $array['metrics'] = $this->preProcessMetrics($form);
        $array['email'] = $form->email();

        foreach ($form->fields() as $name => $field) {
            $field['name'] = $name;

            // Vue relies on a boolean being available on the field itself.
            if (collect($array['columns'])->contains($field['name'])) {
                $field['column'] = true;
            }

            $array['fields'][] = $field;
        }

        return json_encode($array);
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

        // Wat is this italic?
        return view('statamic::forms.create', [
            'title' => t('creating_formset')
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', FormContract::class);

        $data = $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
            'blueprint' => 'nullable|array',
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
        $form = Form::find($form);

        $this->authorize('edit', $form);

        $formset = $this->getFormsetJson($form);

        return view('statamic::forms.edit', compact('form', 'formset'));
    }

    public function update($form)
    {
        $form = Form::find($form);

        $this->authorize('edit', $form);

        $this->hydrateForm($form, $data);
        $form->save();

        $this->success(__('Saved'));

        return [
            'success' => true,
            'redirect' => $form->editUrl()
        ];
    }

    protected function hydrateForm($form, $data)
    {
        return $form
            ->title($data['title'])
            ->handle($data['handle'])
            ->blueprint(collect($data['blueprint'])->first());
    }

    /**
     * Clean up the metric values from the Grid + Array field
     *
     * @return array
     */
    private function prepareMetrics()
    {
        $metrics = [];

        foreach ($this->request->input('formset.metrics') as $metric) {
            foreach ($metric['params'] as $param) {
                $metric[$param['value']] = $param['text'];
            }

            unset($metric['params'], $metric['_id']);

            $metrics[] = $metric;
        }

        return $metrics;
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

    /**
     * Get the columns array
     *
     * @return array
     */
    private function prepareColumns()
    {
        return collect($this->request->input('formset.fields'))->filter(function ($field) {
            return array_get($field, 'column');
        })->map(function ($field) {
            return $field['name'];
        })->values()->all();
    }

    /**
     * Get an array of submitted fields, keyed by the field names
     *
     * @return array
     */
    private function prepareFields()
    {
        $fields = [];

        foreach ($this->request->input('form.fields') as $field) {
            $field_name = $field['name'];
            unset($field['name'], $field['column']);
            $fields[$field_name] = $field;
        }

        return $fields;
    }

    public function destroy($form)
    {
        $form = Form::find($form);

        $this->authorize('delete', $form, 'You are not authorized to delete this form.');

        $form->delete();
    }
}
