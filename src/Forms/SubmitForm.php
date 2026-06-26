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

    public function submit(array $data, array $files = []): SubmissionResult
    {
        $files = $this->normalizeFiles($files);
        $values = array_merge($data, $files);
        $uploadedAssets = [];

        $this->validate($data, $files);

        $this->submission = $this->submission ?? $this->form->makeSubmission()->asPartial()->site($this->site());

        try {
            if ($this->shouldFinalize()) {
                throw_if(Arr::get($values, $this->form->honeypot()), new SilentFormFailureException($this->submission));
            }

            $uploadedAssets = $this->submission->uploadFiles($files);

            $values = array_merge($values, $uploadedAssets);

            $processedValues = $this->form->blueprint()
                ->fields()
                ->addValues($values)
                ->process()
                ->values()
                ->when($this->page, fn ($fields) => $fields->only($this->fieldHandles($this->page)));

            $this->submission->merge($processedValues);

            if ($this->shouldFinalize()) {
                throw_if(FormSubmitted::dispatch($this->submission) === false, new SilentFormFailureException($this->submission));
            }
        } catch (ValidationException|SilentFormFailureException $e) {
            $this->removeUploadedAssets($uploadedAssets);

            throw $e;
        }

        $this->shouldFinalize() ? $this->submission->finalize() : $this->submission->save();

        // todo: obviously this should depend on logic at some point
        $pages = $this->form->formFields()->pages();
        $currentPageIndex = $pages->where('id', $this->page)->keys()->first();
        $nextPage = $pages->get($currentPageIndex + 1);

        return new SubmissionResult(
            $this->submission,
            $nextPage ? $nextPage['id'] : null
        );
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

    private function site()
    {
        $previousUrl = ($referrer = request()->header('referer'))
            ? url()->to($referrer)
            : session()->previousUrl();

        return $previousUrl ? Site::findByUrl($previousUrl) : null;
    }

    private function shouldFinalize(): bool
    {
        // todo: should take logic into account (the actual last page on the form might not be the user's last page)
        $pages = $this->form->formFields()->pages();

        return Arr::get($pages->last(), 'id') === $this->page;
    }

    private function fieldHandles(string $page): array
    {
        return $this->form->blueprint()->tabs()
            ->filter(fn ($tab): bool => $tab->handle() == $page)
            ->flatMap(fn ($tab): array => $tab->sections()->flatMap(fn ($section) => $section->fields()->items()->pluck('handle'))->all())
            ->values()
            ->all();
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

    public function validate(array $data, array $files = [], ?array $only = null): void
    {
        $files = $this->normalizeFiles($files);
        $fields = $this->form->blueprint()->fields()->addValues(array_merge($data, $files));

        $validator = $fields
            ->validator()
            ->withRules($this->extraRules($fields))
            ->validator();

        if (! $only && $this->page) {
            $only = $this->fieldHandles($this->page);
        }

        if ($only) {
            $validator->setRules($this->filterRules($validator->getRulesWithoutPlaceholders(), $only));
        }

        $this->withLocale($this->site()?->lang(), fn () => $validator->validate());
    }

    private function extraRules($fields): array
    {
        return $fields->all()
            ->filter(fn ($field): bool => $field->fieldtype()->handle() === 'assets')
            ->mapWithKeys(fn ($field): array => [$field->handle().'.*' => ['file', new AllowedFile]])
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
            $regex = '/^'.str_replace('\*', '[^.]+', preg_quote($pattern, '/')).'$/';

            if (preg_match($regex, $attribute)) {
                return true;
            }
        }

        return false;
    }
}
