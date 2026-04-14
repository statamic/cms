<?php

namespace Statamic\Fields;

use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Support\Arr;
use Statamic\Support\Str;

use function Statamic\trans as __;

class Tab
{
    protected $handle;
    protected $contents = [];

    public function __construct($handle)
    {
        $this->handle = $handle;
    }

    public function handle(): ?string
    {
        return $this->handle;
    }

    public function setContents(array $contents)
    {
        $this->contents = $contents;

        return $this;
    }

    public function contents(): array
    {
        return $this->contents;
    }

    public function fields(): Fields
    {
        $sections = Arr::get($this->contents, 'sections');

        // Handle situation where there's only fields defined, and not nested under sections.
        // Temporary?
        if (! $sections) {
            $sections = [
                [
                    'fields' => Arr::get($this->contents, 'fields', []),
                ],
            ];
        }

        $fields = collect($sections)->reduce(function ($carry, $section) {
            return $carry->merge(Arr::get($section, 'fields', []));
        }, collect())->all();

        return new Fields($fields);
    }

    public function sections()
    {
        $sections = Arr::get($this->contents, 'sections');

        // Handle situation where there's only fields defined, and not nested under sections.
        // Temporary?
        if (! $sections) {
            $sections = [
                [
                    'fields' => Arr::get($this->contents, 'fields', []),
                ],
            ];
        }

        return collect($sections)
            ->map(function ($section) {
                return new Section($section);
            });
    }

    public function toPublishArray()
    {
        return [
            'display' => $this->display(),
            'instructions' => $this->instructions(),
            'handle' => $this->handle,
            'sections' => $this->expandedSections(),
        ];
    }

    public function display()
    {
        return Arr::get($this->contents, 'display', __(Str::humanize($this->handle)));
    }

    public function instructions()
    {
        return Arr::get($this->contents, 'instructions');
    }

    private function expandedSections(): array
    {
        return $this->sections()->reduce(function ($carry, Section $section) {
            $fields = Arr::get($section->contents(), 'fields', []);

            if (empty($fields)) {
                return $carry->push($section->toPublishArray());
            }

            $sectionContents = $section->contents();
            $sectionMeta = Arr::except($sectionContents, ['fields']);
            $bufferedFields = [];

            foreach ($fields as $field) {
                $importedFieldset = $this->sectionedFieldsetImport($field);

                if (! $importedFieldset) {
                    $bufferedFields[] = $field;

                    continue;
                }

                if (! empty($bufferedFields)) {
                    $carry->push((new Section($sectionMeta + ['fields' => $bufferedFields]))->toPublishArray());
                    $bufferedFields = [];
                }

                foreach ($importedFieldset->sections() as $importedSection) {
                    $carry->push($this->toImportedPublishSection($importedSection, $field));
                }
            }

            if (! empty($bufferedFields)) {
                $carry->push((new Section($sectionMeta + ['fields' => $bufferedFields]))->toPublishArray());
            }

            return $carry;
        }, collect())->all();
    }

    private function sectionedFieldsetImport(array $field): ?Fieldset
    {
        if (! isset($field['import'])) {
            return null;
        }

        if (($field['section_behavior'] ?? 'preserve') === 'flatten') {
            return null;
        }

        $fieldset = FieldsetRepository::find($field['import']);

        return $fieldset && $fieldset->hasSections() ? $fieldset : null;
    }

    private function toImportedPublishSection(array $section, array $import): array
    {
        $fields = (new Fields(Arr::get($section, 'fields', [])))->all();

        if ($overrides = $import['config'] ?? null) {
            $fields = $fields->map(function ($field, $handle) use ($overrides) {
                return $field->setConfig(array_merge($field->config(), $overrides[$handle] ?? []));
            });
        }

        if ($prefix = Arr::get($import, 'prefix')) {
            $fields = $fields->mapWithKeys(function ($field) use ($prefix) {
                $field = clone $field;
                $handle = $prefix.$field->handle();
                $fieldPrefix = $prefix.$field->prefix();

                return [$handle => $field->setHandle($handle)->setPrefix($fieldPrefix)];
            });
        }

        return Arr::removeNullValues([
            'display' => Arr::get($section, 'display'),
            'instructions' => Arr::get($section, 'instructions'),
            'collapsible' => ($collapsible = Arr::get($section, 'collapsible')) ?: null,
            'collapsed' => ($collapsible && Arr::get($section, 'collapsed')) ?: null,
        ]) + [
            'fields' => $fields->map->toPublishArray()->values()->all(),
        ];
    }
}
