<?php

namespace Statamic\Forms;

use Facades\Statamic\Fields\Validator as FieldValidator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Events\FormSubmitted;
use Statamic\Exceptions\FormRestrictedException;
use Statamic\Exceptions\SilentFormFailureException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Site;
use Statamic\Forms\Logic\PageLogic;
use Statamic\Rules\AllowedFile;
use Statamic\Support\Arr;

class SubmitForm
{
    use Localizable;

    protected Form $form;
    protected ?string $page = null;
    protected ?Submission $submission = null;

    public function form(Form $form): static
    {
        $this->form = $form;

        return $this;
    }

    public function page(string $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function resume(Submission $submission): static
    {
        $this->submission = $submission;

        return $this;
    }

    public function submission(): ?Submission
    {
        return $this->submission;
    }

    public function submit(array $data, array $files = []): SubmissionResult
    {
        throw_if($this->form->restricted(), new FormRestrictedException($this->form));

        $nextPage = null;
        $uploadedAssets = [];
        $files = $this->normalizeFiles($files);
        $values = array_merge($data, $files);

        $this->validate($data, $files);

        $this->submission = $this->submission ?? $this->form->makeSubmission()->asPartial()->site($this->site());

        try {
            $uploadedAssets = $this->submission->uploadFiles($files);

            $values = array_merge($values, $uploadedAssets);

            $processedValues = $this->form->blueprint()
                ->fields()
                ->addValues($values)
                ->process()
                ->values()
                ->when($this->page, fn ($fields) => $fields->only($this->fieldHandles($this->page)));

            $this->submission->merge($processedValues);

            $nextPage = $this->resolveNextPage();

            if ($this->shouldFinalize($nextPage) && ! $this->hasCompletedEveryPage()) {
                $nextPage = Arr::get($this->form->formFields()->pages()->first(), 'id');
            }

            if ($this->shouldFinalize($nextPage)) {
                if (Arr::get($values, $this->form->honeypot())) {
                    $this->rejectHoneypotSubmission();
                }

                $accepted = FormSubmitted::dispatch($this->submission) !== false;

                if ($this->submission->isSpam()) {
                    $this->rejectSpamSubmission();
                }

                throw_unless($accepted, new SilentFormFailureException);
            }
        } catch (ValidationException|SilentFormFailureException $e) {
            $this->cleanUpUploads($uploadedAssets);

            throw $e;
        }

        $this->shouldFinalize($nextPage) ? $this->submission->finalize() : $this->submission->save();

        return new SubmissionResult($this->submission, $nextPage);
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
            ->filter(fn ($field) => in_array($field->fieldtype()->handle(), ['assets', 'files', 'form_upload']))
            ->keys();

        foreach ($assetFields as $handle) {
            if (isset($files[$handle])) {
                $files[$handle] = Arr::wrap($files[$handle]);
            }
        }

        return $files;
    }

    private function site()
    {
        $previousUrl = ($referrer = request()->header('referer'))
            ? url()->to($referrer)
            : session()->previousUrl();

        return $previousUrl ? Site::findByUrl($previousUrl) : null;
    }

    private function resolveNextPage(): ?string
    {
        if (! $this->form->hasMultiplePages()) {
            return null;
        }

        return (new PageLogic($this->form))->nextPage($this->page, $this->submission->data()->all());
    }

    private function shouldFinalize(?string $nextPage): bool
    {
        return ! $this->form->hasMultiplePages() || ! $nextPage;
    }

    private function hasCompletedEveryPage(): bool
    {
        if (! $this->form->hasMultiplePages()) {
            return true;
        }

        $data = $this->submission->data()->all();

        $pageHandles = collect((new PageLogic($this->form))->path($data))
            ->flatMap(fn (string $id): array => $this->fieldHandles($id));

        return $this->form->blueprint()->fields()->all()
            ->filter(fn ($field): bool => $pageHandles->contains($field->handle()) && $field->isRequired())
            ->every(fn ($field): bool => $this->fieldHasValue($data, $field->handle()));
    }

    private function fieldHasValue(array $data, string $handle): bool
    {
        $value = Arr::get($data, $handle);

        return $value !== null && $value !== '' && $value !== [];
    }

    private function fieldHandles(string $page): array
    {
        return $this->form->blueprint()->tabs()
            ->filter(fn ($tab): bool => $tab->handle() === $page)
            ->flatMap(fn ($tab): array => $tab->sections()->flatMap(fn ($section) => $section->fields()->all()->keys())->all())
            ->values()
            ->all();
    }

    private function rejectHoneypotSubmission(): void
    {
        if ($this->form->get('honeypot_behavior', 'ignore') === 'mark_as_spam') {
            $this->submission->markAsSpam();
        }

        $this->rejectSpamSubmission();
    }

    private function rejectSpamSubmission(): void
    {
        if ($this->form->store()) {
            if ($this->submission->isSpam()) {
                $this->submission->save();
            }
        } elseif ($this->form->submission($this->submission->id())) {
            $this->submission->deleteQuietly();
        }

        throw new SilentFormFailureException;
    }

    private function cleanUpUploads(array $uploads): void
    {
        $assets = $this->uploadsOfType($uploads, ['assets']);

        $this->removeUploadedAssets($assets);

        if ($this->isRetainedSpam()) {
            $this->forgetRemovedAssets($assets);

            return;
        }

        $this->removeTemporaryUploads($uploads);
    }

    private function isRetainedSpam(): bool
    {
        return $this->form->store()
            && $this->submission->isSpam()
            && $this->form->submission($this->submission->id());
    }

    private function forgetRemovedAssets(array $assets): void
    {
        if (! $assets) {
            return;
        }

        foreach (array_keys($assets) as $handle) {
            $this->submission->remove($handle);
        }

        $this->submission->saveQuietly();
    }

    private function uploadsOfType(array $uploads, array $types): array
    {
        $fields = $this->form->blueprint()->fields()->all();

        return collect($uploads)
            ->filter(fn ($value, $handle) => in_array($fields->get($handle)?->type(), $types))
            ->all();
    }

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

    private function removeTemporaryUploads(array $uploads): void
    {
        $disk = Storage::disk(config('statamic.system.file_uploads_disk', 'local'));
        $formUploadsPath = config('statamic.forms.file_uploads_path', 'statamic/form-uploads');
        $filesPath = config('statamic.system.file_uploads_path', 'statamic/file-uploads');

        // A persisted partial may be resumed, and its earlier pages' uploads belong to it.
        if ($this->form->submission($this->submission->id())) {
            $this->temporaryPaths($uploads, 'form_upload')->each(fn ($path) => $disk->delete("{$formUploadsPath}/{$path}"));
        } else {
            $disk->deleteDirectory("{$formUploadsPath}/{$this->submission->id()}");
        }

        $this->temporaryPaths($uploads, 'files')->each(fn ($path) => $disk->delete("{$filesPath}/{$path}"));
    }

    private function temporaryPaths(array $uploads, string $type)
    {
        return collect($this->uploadsOfType($uploads, [$type]))
            ->flatten()
            ->filter(fn ($path) => is_string($path) && $path !== '' && ! str_contains($path, '..'));
    }

    public function validate(array $data, array $files = [], ?array $only = null): void
    {
        $files = $this->normalizeFiles($files);
        $fields = $this->form->blueprint()->fields()->addValues(array_merge($data, $files));

        $validator = $fields->validator()->validator();

        $validator->setRules($this->withFileUploadRules($validator->getRulesWithoutPlaceholders(), $fields, $files));

        if (! $only && $this->page) {
            $only = $this->fieldHandles($this->page);
        }

        if ($only) {
            $validator->setRules($this->filterRules($validator->getRulesWithoutPlaceholders(), $only));
        }

        $this->withLocale($this->site()?->lang(), fn () => $validator->validate());
    }

    private function withFileUploadRules(array $rules, $fields, array $files): array
    {
        return $fields->all()
            ->filter(fn ($field): bool => in_array($field->fieldtype()->handle(), ['assets', 'files', 'form_upload']))
            ->reduce(function ($rules, $field) use ($files) {
                $handle = $field->handle();

                // Freshly uploaded files should be validated as files.
                if (array_key_exists($handle, $files)) {
                    $shouldBeStoredAsAsset = $field->fieldtype()->handle() === 'assets'
                        || ($field->fieldtype()->handle() === 'form_upload' && $field->fieldtype()->config('store'));

                    $fileRules = $shouldBeStoredAsAsset
                        ? array_merge(['file', new AllowedFile], $this->assetContainerRules($field))
                        : ['file', new AllowedFile($field->fieldtype()->config('allowed_extensions'))];

                    $rules["{$handle}.*"] = collect($rules["{$handle}.*"] ?? [])
                        ->merge($fileRules)
                        ->unique()
                        ->values()
                        ->all();

                    return $rules;
                }

                // Anything not present in $files must already be stored against the field. Removing
                // some of an existing multi-file value is fine, but a value that was never uploaded
                // gets rejected - an unvalidated one could be used to read/delete arbitrary files
                // when the submission is finalized.
                $stored = Arr::wrap($this->submission?->get($handle));

                if (collect(Arr::wrap($field->value()))->contains(fn ($value) => ! in_array($value, $stored, true))) {
                    $rules[$handle] = array_merge($rules[$handle] ?? [], ['prohibited']);

                    return $rules;
                }

                if (is_array($field->value()) || ! isset($rules[$handle])) {
                    return $rules;
                }

                $rules[$handle] = collect($rules[$handle])
                    ->reject(fn ($rule) => $rule === 'array' || str_starts_with($rule, 'max:') || str_starts_with($rule, 'min:'))
                    ->values()
                    ->all();

                return $rules;
            }, $rules);
    }

    private function assetContainerRules($field): array
    {
        $configured = $field->fieldtype()->config('container');

        $container = $configured
            ? AssetContainer::find($configured)
            : (($containers = AssetContainer::all())->count() === 1 ? $containers->first() : null);

        return collect($container?->validationRules())
            ->map(fn ($rule) => FieldValidator::parse($rule))
            ->all();
    }

    private function filterRules(array $rules, array $only): array
    {
        return collect($rules)
            ->filter(fn ($rule, $attribute): bool => $this->shouldValidate($attribute, $only))
            ->all();
    }

    private function shouldValidate(string $attribute, array $only): bool
    {
        foreach ($only as $pattern) {
            // A handle also covers its nested/array attributes, e.g. "document" matches "document.0".
            $regex = '/^'.str_replace('\*', '[^.]+', preg_quote($pattern, '/')).'($|\..*)/';

            if (preg_match($regex, $attribute)) {
                return true;
            }
        }

        return false;
    }
}
