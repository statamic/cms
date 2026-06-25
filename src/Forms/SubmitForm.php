<?php

namespace Statamic\Forms;

use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Events\FormSubmitted;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\Asset;
use Statamic\Facades\Site;
use Statamic\Rules\AllowedFile;
use Statamic\Support\Arr;

class SubmitForm
{
    use Localizable;

    protected Form $form;
    protected ?Submission $submission = null;
    protected bool $partial = false;

    public function form(Form $form): static
    {
        $this->form = $form;

        return $this;
    }

    public function resume(Submission $submission): static
    {
        $this->submission = $submission;

        return $this;
    }

    public function asPartial(bool $partial = true): static
    {
        $this->partial = $partial;

        return $this;
    }

    public function submit(array $data, array $files = [], ?array $only = null): Submission
    {
        $files = $this->normalizeFiles($files);
        $values = array_merge($data, $files);

        $this->validate($data, $files, $only);

        $submission = $this->submission ?? $this->form->makeSubmission()->asPartial()->site($this->site());

        $uploadedAssets = [];

        try {
            if (! $this->partial) {
                throw_if(Arr::get($values, $this->form->honeypot()), new SilentFormFailureException($submission));
            }

            $uploadedAssets = $submission->uploadFiles($files);

            $values = array_merge($values, $uploadedAssets);

            $processedValues = $this->form->blueprint()
                ->fields()
                ->addValues($values)
                ->process()
                ->values()
                ->when($this->submission || $this->partial, fn ($processedValues) => $processedValues->only(array_keys($values)));

            $submission->merge($processedValues);

            if (! $this->partial) {
                throw_if(FormSubmitted::dispatch($submission) === false, new SilentFormFailureException($submission));
            }
        } catch (ValidationException|SilentFormFailureException $e) {
            $this->removeUploadedAssets($uploadedAssets);

            throw $e;
        }

        $this->partial ? $submission->save() : $submission->finalize();

        return $submission;
    }

    public function validate(array $data, array $files = [], ?array $only = null): void
    {
        $files = $this->normalizeFiles($files);
        $fields = $this->form->blueprint()->fields()->addValues(array_merge($data, $files));

        $validator = $fields
            ->validator()
            ->withRules($this->extraRules($fields))
            ->validator();

        if ($only !== null) {
            $validator->setRules($this->filterRules($validator->getRulesWithoutPlaceholders(), $only));
        }

        $this->withLocale($this->site()?->lang(), fn () => $validator->validate());
    }

    private function extraRules($fields): array
    {
        return $fields->all()
            ->filter(fn ($field) => $field->fieldtype()->handle() === 'assets')
            ->mapWithKeys(fn ($field) => [$field->handle().'.*' => ['file', new AllowedFile]])
            ->all();
    }

    private function filterRules(array $rules, array $only): array
    {
        return collect($rules)
            ->filter(fn ($rule, $attribute) => $this->shouldValidate($attribute, $only))
            ->all();
    }

    private function shouldValidate(string $attribute, array $only): bool
    {
        foreach ($only as $pattern) {
            $regex = '/^'.str_replace('\*', '[^.]+', preg_quote($pattern, '/')).'$/';

            if (preg_match($regex, $attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize uploaded files to arrays.
     *
     * The assets fieldtype expects arrays, even for `max_files: 1`,
     * but we don't want to force that on the front end.
     */
    private function normalizeFiles(array $files): array
    {
        $assetFields = $this->form->blueprint()->fields()->all()
            ->filter(fn ($field) => in_array($field->fieldtype()->handle(), ['assets', 'files']))
            ->keys();

        foreach ($assetFields as $handle) {
            if (isset($files[$handle])) {
                $files[$handle] = Arr::wrap($files[$handle]);
            }
        }

        return $files;
    }

    /**
     * Remove any uploaded assets.
     *
     * Triggered by a validation exception or silent failure.
     */
    private function removeUploadedAssets(array $assets): void
    {
        collect($assets)
            ->flatten()
            ->each(function ($id) {
                if ($asset = Asset::find($id)) {
                    $asset->delete();
                }
            });
    }

    private function site()
    {
        $previousUrl = ($referrer = request()->header('referer'))
            ? url()->to($referrer)
            : session()->previousUrl();

        return $previousUrl ? Site::findByUrl($previousUrl) : null;
    }
}
