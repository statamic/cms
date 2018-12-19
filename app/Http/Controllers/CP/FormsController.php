<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Str;
use Statamic\API\Form;

class FormsController extends CpController
{
    public function index()
    {
        $this->authorize('forms');

        return view('statamic::forms.index', [
            'forms' => Form::all()->toArray()
        ]);
    }

    public function show($form)
    {
        $this->authorize('forms');

        if (! $form = Form::get($form)) {
            return $this->pageNotFound();
        }

        return view('statamic::forms.show', compact('form'));
    }

    public function getFormsetJson($form)
    {
        $array = $form->toArray();

        $array['honeypot'] = $form->honeypot();

        $array['columns'] = array_keys($form->columns());

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
        $this->authorize('super');

        return view('statamic::forms.create', [
            'title' => t('creating_formset')
        ]);
    }

    public function store()
    {
        $this->authorize('forms');

        $slug = ($this->request->has('slug'))
                ? $this->request->input('slug')
                : Str::slug($this->request->input('formset.title'), '_');

        $form = Form::create($slug);

        $form->title($this->request->input('formset.title'));
        $form->honeypot($this->request->input('formset.honeypot'));
        $form->columns($this->prepareColumns());
        $form->fields($this->prepareFields());
        $form->metrics($this->prepareMetrics());
        $form->email($this->prepareEmail());

        $form->save();

        $this->success(__('Created'));

        return [
            'success' => true,
            'redirect' => $form->editUrl()
        ];
    }

    public function edit($form)
    {
        $this->authorize('super');

        $form = Form::get($form);
        $formset = $this->getFormsetJson($form);

        return view('statamic::forms.edit', compact('form', 'formset'));
    }

    public function update($form)
    {
        $this->authorize('forms');

        $form = Form::get($form);

        $form->title($this->request->input('formset.title'));
        $form->honeypot($this->request->input('formset.honeypot'));
        $form->columns($this->prepareColumns());
        $form->metrics($this->prepareMetrics());
        $form->email($this->prepareEmail());
        $form->fields($this->prepareFields());

        $form->save();

        $this->success(__('Saved'));

        return [
            'success' => true,
            'redirect' => $form->editUrl()
        ];
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

        foreach ($this->request->input('formset.fields') as $field) {
            $field_name = $field['name'];
            unset($field['name'], $field['column']);
            $fields[$field_name] = $field;
        }

        return $fields;
    }
}
