<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Time extends Fieldtype
{
    protected $categories = ['special'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'seconds_enabled' => [
                        'display' => __('Show Seconds'),
                        'instructions' => __('statamic::fieldtypes.time.config.seconds_enabled'),
                        'type' => 'toggle',
                        'default' => false,
                    ],
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }

    public function process($data)
    {
        $time = $this->formatTime($data);

        if ($time === '00:00' || $time === '00:00:00') {
            return null;
        }

        return $time;
    }

    private function formatTime($time)
    {
        $parts = explode(':', $time);

        if (count($parts) === 1) {
            $parts[0] = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $parts[1] = '00';
            if ($this->config('seconds_enabled')) {
                $parts[2] = '00';
            }
        }

        if (count($parts) === 2) {
            $parts[1] = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
            if (strlen($parts[1]) > 2) {
                $parts[1] = substr($parts[1], 0, 2);
            }
            if ($this->config('seconds_enabled')) {
                $parts[2] = '00';
            }
        }

        if (count($parts) === 3) {
            $parts[2] = str_pad($parts[2], 2, '0', STR_PAD_LEFT);
            if (strlen($parts[2]) > 2) {
                $parts[2] = substr($parts[2], 0, 2);
            }
        }

        $time = implode(':', $parts);

        return $time;
    }
}
