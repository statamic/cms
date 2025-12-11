<?php

namespace Statamic\Dictionaries;

use Statamic\Facades\Antlers;
use Statamic\Facades\YAML;

class File extends BasicDictionary
{
    protected array $keywords = ['files', 'file', 'json', 'csv', 'yaml', 'yml'];

    protected function fieldItems()
    {
        return [
            'filename' => [
                'type' => 'slug',
                'display' => __('Filename'),
                'instructions' => __('statamic::fieldtypes.dictionary.file.config.filename'),
                'validate' => ['required'],
            ],
            'label' => [
                'type' => 'text',
                'display' => __('Label'),
                'instructions' => __('statamic::fieldtypes.dictionary.file.config.label'),
            ],
            'value' => [
                'type' => 'text',
                'display' => __('Value'),
                'instructions' => __('statamic::fieldtypes.dictionary.file.config.value'),
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

        if (! file_exists($path)) {
            throw new \Exception('Dictionary file ['.$path.'] does not exist.');
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return match ($extension) {
            'json' => json_decode(file_get_contents($path), true),
            'yaml' => YAML::file($path)->parse(),
            'csv' => $this->fromCsv($path),
        };
    }

    private function fromCsv(string $path): array
    {
        $rows = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rows[] = array_combine($headers, $data);
            }
            fclose($handle);
        }

        return $rows;
    }
}
