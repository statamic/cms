<?php

namespace Statamic\Forms;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Contracts\Query\Builder;
use Statamic\Facades\Form as FormFacade;
use Statamic\Facades\GlobalSet;
use Statamic\Fields\LabeledValue;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\AutocompleteEditor\Mentions;
use Statamic\Fieldtypes\FormUpload;
use Statamic\Fieldtypes\Markdown;
use Statamic\Fieldtypes\Relationship;
use Statamic\Support\Arr;
use Stringable;

use function Statamic\trans as __;

class SubmissionMentions
{
    private const EXCLUDED_GLOBAL_FIELDTYPES = [
        'bard', 'replicator', 'grid', 'group', 'table', 'array', 'yaml', 'code',
        'section', 'spacer', 'revealer', 'html', 'hidden', 'icon',
    ];

    private const EXCLUDED_FORM_VALUES = ['id', 'date', 'form'];

    public static function options(Form $form): array
    {
        $options = [];

        foreach ($form->formFields()->fields() as $handle => $field) {
            if ($field->fieldtype()->collectsValue()) {
                $options[$handle] = __($field->display());
            }
        }

        $others = [
            'form.title' => __('Form Title'),
            'site.name' => __('Site Name'),
            'site.url' => __('Site URL'),
        ];

        foreach (FormFacade::mentions() as $handle => $mention) {
            $others[$handle] = __($mention['label']);
        }

        foreach (GlobalSet::all() as $set) {
            foreach ($set->blueprint()?->fields()->all() ?? [] as $handle => $field) {
                if (static::isGlobalFieldOffered($field->fieldtype(), $field->type())) {
                    $others["globals.{$set->handle()}.{$handle}"] = __($set->title()).': '.__($field->display());
                }
            }
        }

        asort($others, SORT_NATURAL | SORT_FLAG_CASE);

        return $options + $others;
    }

    public static function handlesIn(string ...$contents): array
    {
        preg_match_all(Mentions::TOKEN, implode("\n", $contents), $matches);

        return array_values(array_unique($matches[1]));
    }

    public static function values(Submission $submission, array $handles, ?array $submissionData = null): array
    {
        $registered = FormFacade::mentions();
        $globals = [];
        $values = [];

        foreach ($handles as $handle) {
            if ($handle === 'form.title') {
                $value = $submission->form()->title();
            } elseif ($handle === 'site.name') {
                $value = $submission->site()->name();
            } elseif ($handle === 'site.url') {
                $value = $submission->site()->absoluteUrl();
            } elseif (isset($registered[$handle])) {
                $value = $registered[$handle]['value']($submission);
            } elseif (str_starts_with($handle, 'globals.')) {
                $value = static::globalValue($submission, $handle, $globals);
            } elseif (in_array($handle, self::EXCLUDED_FORM_VALUES) || ! $submission->form()->fields()->has($handle)) {
                continue;
            } else {
                $submissionData ??= $submission->toAugmentedArray();
                $value = static::formFieldValue($submission, $handle, $submissionData[$handle] ?? null);
            }

            $values[$handle] = static::mentionValue($value);
        }

        return $values;
    }

    public static function replace(Submission $submission, string $content, ?callable $using = null, ?array $submissionData = null): string
    {
        $values = static::values($submission, static::handlesIn($content), $submissionData);

        return Mentions::replace($content, $values, using: $using);
    }

    public static function mentionValue(mixed $value): string
    {
        if ($value instanceof Value) {
            $value = $value->value();
        }

        if ($value instanceof Builder) {
            $value = $value->get();
        }

        if ($value instanceof LabeledValue) {
            return (string) ($value->label() ?? $value->value());
        }

        if (static::isAugmentedOption($value)) {
            return (string) ($value['label'] ?? $value['value']);
        }

        if ($value instanceof Asset) {
            return (string) $value->absoluteUrl();
        }

        if (is_bool($value)) {
            return $value ? __('Yes') : __('No');
        }

        if (is_iterable($value)) {
            return collect($value)
                ->map(fn ($item) => static::mentionValue($item))
                ->filter(fn ($item) => $item !== '')
                ->implode(', ');
        }

        if (is_scalar($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        return '';
    }

    private static function isAugmentedOption(mixed $value): bool
    {
        if (! is_array($value) || count($value) !== 3) {
            return false;
        }

        return array_key_exists('key', $value) && array_key_exists('value', $value) && array_key_exists('label', $value);
    }

    private static function formFieldValue(Submission $submission, string $handle, mixed $value): mixed
    {
        $fieldtype = $submission->form()->fields()->get($handle)->fieldtype();

        if (! $fieldtype instanceof FormUpload || $fieldtype->config('store')) {
            return $value;
        }

        if ($value instanceof Value) {
            $value = $value->value();
        }

        return collect(Arr::wrap($value))->map(fn ($path) => basename($path))->all();
    }

    private static function isGlobalFieldOffered($fieldtype, string $type): bool
    {
        return ! in_array($type, self::EXCLUDED_GLOBAL_FIELDTYPES) && ! $fieldtype instanceof Relationship;
    }

    private static function globalValue(Submission $submission, string $handle, array &$globals): mixed
    {
        [, $setHandle, $fieldHandle] = array_pad(explode('.', $handle, 3), 3, null);

        if (! $setHandle || ! $fieldHandle) {
            return null;
        }

        if (! array_key_exists($setHandle, $globals)) {
            $set = GlobalSet::findByHandle($setHandle);
            $site = $submission->site()->handle();

            $globals[$setHandle] = $set && $set->existsIn($site)
                ? $set->in($site)->toAugmentedArray()
                : [];
        }

        $value = $globals[$setHandle][$fieldHandle] ?? null;

        if ($value instanceof Value && $value->fieldtype() instanceof Markdown) {
            return $value->raw();
        }

        return $value;
    }
}
