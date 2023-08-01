<?php

namespace Statamic\Forms;

use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Contracts\Forms\FormRepository as Contract;
use Statamic\Contracts\Forms\Submission as SubmissionContract;
use Statamic\Facades\File;
use Statamic\Facades\Folder;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FormRepository implements Contract
{
    private $configs = [];

    /**
     * Find a form.
     *
     * @param  string  $handle
     * @return FormContract
     */
    public function find($handle)
    {
        $form = $this->make($handle);

        if (! File::exists($form->path())) {
            return;
        }

        return $form->hydrate();
    }

    /**
     * Get all forms.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return collect(Folder::getFilesByType(config('statamic.forms.forms'), 'yaml'))->map(function ($file) {
            return self::find(pathinfo($file)['filename']);
        });
    }

    /**
     * Get the number of forms.
     *
     * @return int
     */
    public function count()
    {
        return $this->all()->count();
    }

    /**
     * Make form instance.
     *
     * @param  mixed  $handle
     * @return FormContract
     */
    public function make($handle = null)
    {
        $form = app(FormContract::class);

        if ($handle) {
            $form->handle($handle);
        }

        return $form;
    }

    public function addConfig($handles, string $display, array $fields)
    {
        $this->configs[] = [
            'display' => $display,
            'handles' => Arr::wrap($handles),
            'fields' => $fields,
        ];
    }

    public function getConfigFor($handle)
    {
        $reserved = ['name', 'fields', 'submission', 'email'];

        return collect($this->configs)
            ->filter(function ($config) use ($handle) {
                return in_array('*', $config['handles']) || in_array($handle, $config['handles']);
            })
            ->flatMap(function ($config) use ($reserved) {

                return [
                    Str::slugify($config['display']) => [
                        'display' => $config['display'],
                        'fields' => collect($config['fields'])
                            ->filter(fn ($field, $index) => ! in_array($field['handle'] ?? $index, $reserved))
                            ->all(),
                    ],
                ];
            })
            ->all();
    }

    public static function bindings(): array
    {
        return [
            FormContract::class => Form::class,
            SubmissionContract::class => Submission::class,
        ];
    }
}
