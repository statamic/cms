<?php

namespace Statamic\API\Endpoint;

use Request as Req;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Folder;
use Statamic\API\Metrics;

class Form
{
    /**
     * Get a Form
     *
     * @param  string $name
     * @return Statamic\Contracts\Forms\Form
     */
    public function get($name)
    {
        $form = self::create($name);

        $path = resource_path("formsets/{$name}.yaml");

        if (! File::exists($path)) {
            return;
        }

        $formset = $form->formset();
        $formset->data(YAML::parse(File::get($path)));

        $form->formset($formset);

        return $form;
    }

    /**
     * Get all Forms
     *
     * @param  string $name
     * @return array of Statamic\Contracts\Forms\Forms
     */
    public function all()
    {
        $forms = [];
        $files = Folder::getFilesByType(settings_path('formsets'), 'yaml');

        foreach ($files as $file) {
            $filename = pathinfo($file)['filename'];

            $form = self::get($filename);
            $submissions = $form->submissions();

            $form = $form->toArray();
            $form['count'] = count($submissions);
            $form['show_url'] = route('form.show', $form['name']);

            $forms[] = $form;
        }

        return $forms;
    }

    /**
     * Get all Forms
     *
     * @param  string $name
     * @return array of Statamic\Contracts\Forms\Forms
     */
    public function getAllFormsets()
    {
        $forms = [];
        $files = Folder::getFilesByType(settings_path('formsets'), 'yaml');

        foreach ($files as $file) {
            $filename = pathinfo($file)['filename'];
            $form = self::get($filename);
            $form = $form->toArray();
            $form['show_url'] = route('form.show', $form['name']);

            $forms[] = $form;
        }

        return $forms;
    }

    /**
     * Create a form
     *
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function create($name)
    {
        $formset = app('Statamic\Contracts\Forms\Formset');
        $formset->name($name);

        $form = app('Statamic\Contracts\Forms\Form');
        $form->name($name);
        $form->formset($formset);

        return $form;
    }

    public function fields($form)
    {
        $fields = [];
        $form = self::get($form)->formset()->data();

        foreach ($form['fields'] as $key => $field) {
            $fields[] = [
                'field' => $key,
                'name' => $key, //
                'old' => (Req::hasSession()) ? sanitize(old($key)) : ''
            ] + $field;
        }

        return $fields;
    }
}
