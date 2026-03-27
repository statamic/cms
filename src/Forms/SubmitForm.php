<?php

namespace Statamic\Forms;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Events\FormSubmitted;
use Statamic\Events\SubmissionCreated;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades;
use Statamic\Facades\Asset;
use Statamic\Rules\AllowedFile;
use Statamic\Sites\Site;
use Statamic\Support\Arr;

class SubmitForm
{
    use Localizable;

    public function submit(
        Form $form,
        array $data = [],
        array $files = [],
        ?Site $site = null,
    ): Submission {
        $values = array_merge($data, $files);
        $fields = $form->blueprint()->fields();
        $fields = $fields->addValues($values);
        $site = $site ?? Facades\Site::default();

        $uploadedAssets = [];
        $files = $this->normalizeFiles($form, $files);

        $this->withLocale($site->lang(), fn () => $this->validator($form, $data, $files)->validate());

        $submission = $form->makeSubmission();

        try {
            throw_if(Arr::get($values, $form->honeypot()), new SilentFormFailureException($submission));

            $uploadedAssets = $submission->uploadFiles($files);

            $values = array_merge($values, $uploadedAssets);

            $submission->data(
                $fields->addValues($values)->process()->values()
            );

            // If any event listeners return false, we'll do a silent failure.
            // If they want to add validation errors, they can throw an exception.
            throw_if(FormSubmitted::dispatch($submission) === false, new SilentFormFailureException($submission));
        } catch (ValidationException|SilentFormFailureException $e) {
            $this->removeUploadedAssets($uploadedAssets);

            throw $e;
        }

        if ($form->store()) {
            $submission->save();
        } else {
            // When the submission is saved, this same created event will be dispatched.
            // We'll also fire it here if submissions are not configured to be stored
            // so that developers may continue to listen and modify it as needed.
            SubmissionCreated::dispatch($submission);
        }

        SendEmails::dispatch($submission, $site);

        return $submission;
    }

    public function validator(Form $form, array $data, array $files = []): Validator
    {
        $values = array_merge($data, $files);
        $fields = $form->blueprint()->fields()->addValues($values);

        return $fields
            ->validator()
            ->withRules($this->extraRules($fields))
            ->validator();
    }

    private function extraRules($fields): array
    {
        return $fields->all()
            ->filter(fn ($field) => $field->fieldtype()->handle() === 'assets')
            ->mapWithKeys(function ($field) {
                return [$field->handle().'.*' => ['file', new AllowedFile]];
            })
            ->all();
    }

    /**
     * Normalize uploaded files to arrays.
     *
     * The assets fieldtype expects arrays, even for `max_files: 1`,
     * but we don't want to force that on the front end.
     */
    private function normalizeFiles(Form $form, array $files): array
    {
        $assetFields = $form->blueprint()->fields()->all()
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
}
