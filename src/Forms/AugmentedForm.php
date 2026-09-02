<?php

namespace Statamic\Forms;

use Statamic\Data\AbstractAugmented;
use Statamic\Fields\Section;
use Statamic\Fields\Tab;
use Statamic\Statamic;

class AugmentedForm extends AbstractAugmented
{
    public function keys()
    {
        $keys = ['handle', 'title', 'fields', 'sections', 'pages', 'status', 'api_url'];

        if (! Statamic::isApiRoute()) {
            $keys[] = 'honeypot';
        }

        return $keys;
    }

    public function sections(): array
    {
        return $this->data->sections()
            ->map(fn (Section $section) => $this->sectionToArray($section))
            ->all();
    }

    public function pages(): array
    {
        return $this->data->pages()->map(fn (Tab $page) => [
            'id' => $page->handle(),
            'display' => $page->display(),
            'instructions' => $page->instructions(),
            'sections' => $page->sections()->map(fn (Section $section) => $this->sectionToArray($section))->all(),
        ])->all();
    }

    private function sectionToArray(Section $section): array
    {
        return [
            'display' => $section->display(),
            'instructions' => $section->instructions(),
            'fields' => $section->fields()->all()->map->toArray()->all(),
        ];
    }
}
