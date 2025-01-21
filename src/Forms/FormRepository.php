<?php

namespace Statamic\Forms;

use Closure;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Contracts\Forms\FormRepository as Contract;
use Statamic\Contracts\Forms\Submission as SubmissionContract;
use Statamic\Exceptions\FormNotFoundException;
use Statamic\Facades\File;
use Statamic\Facades\Folder;
use Statamic\Forms\Exporters\ExporterRepository;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FormRepository implements Contract
{
    private $configs = [];
    private $redirects = [];

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

    public function findOrFail($handle): FormContract
    {
        $form = $this->find($handle);

        if (! $form) {
            throw new FormNotFoundException($handle);
        }

        return $form;
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

    public function appendConfigFields($handles, string $display, array $fields)
    {
        $this->configs[] = [
            'display' => $display,
            'handles' => Arr::wrap($handles),
            'fields' => $fields,
        ];
    }

    public function extraConfigFor($handle)
    {
        $reserved = ['title', 'honeypot', 'store', 'email'];

        return collect($this->configs)
            ->filter(function ($config) use ($handle) {
                return in_array('*', $config['handles']) || in_array($handle, $config['handles']);
            })
            ->flatMap(function ($config) use ($reserved) {

                return [
                    Str::snake($config['display']) => [
                        'display' => $config['display'],
                        'fields' => collect($config['fields'])
                            ->filter(fn ($field, $index) => ! in_array($field['handle'] ?? $index, $reserved))
                            ->all(),
                    ],
                ];
            })
            ->all();
    }

    public function redirect(string $form, Closure $callback)
    {
        $this->redirects[$form] = $callback;

        return $this;
    }

    public function getSubmissionRedirect(SubmissionContract $submission)
    {
        $callback = $this->redirects[$submission->form()->handle()] ?? fn () => null;

        return $callback($submission);
    }

    public function exporters()
    {
        return app(ExporterRepository::class);
    }

    public static function bindings(): array
    {
        return [
            FormContract::class => Form::class,
            SubmissionContract::class => Submission::class,
        ];
    }
}
