<?php

namespace Statamic\Dictionaries;

use Statamic\Facades\Antlers;
use Statamic\Facades\YAML;

class File extends BasicDictionary
{
    protected function fieldItems()
    {
        return [
            'filename' => [
                'type' => 'text',
            ],
            'label' => [
                'type' => 'text',
                'display' => __('Label'),
                'instructions' => 'Antlers is supported.',
            ],
            'value' => [
                'type' => 'text',
                'display' => __('Value'),
            ],
        ];
    }

    public function setConfig(array $config): Dictionary
    {
        if ($value = $config['value'] ?? null) {
            $this->valueKey = $value;
        }
        if ($label = $config['label'] ?? null) {
            $this->labelKey = $label;
        }

        return parent::setConfig($config);
    }

    protected function getItemLabel(array $item): string
    {
        if (str_contains($this->labelKey, '{{')) {
            return (string) Antlers::parse($this->labelKey, $item);
        }

        return parent::getItemLabel($item);
    }

    protected function getItems(): array
    {
        $path = resource_path('dictionaries').'/'.$this->config['filename'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return match ($extension) {
            'json' => json_decode(file_get_contents($path), true),
            'yaml' => YAML::file($path)->parse(),
        };
    }
}
