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
        $keys = ['handle', 'title', 'fields', 'sections', 'pages', 'status', 'require_login', 'restriction_message', 'api_url'];

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

    public function requireLogin(): bool
    {
        return (bool) $this->data->get('require_login');
    }

    public function restrictionMessage(): ?string
    {
        return $this->data->status() === 'open' ? null : $this->data->restrictionMessage();
    }
}
