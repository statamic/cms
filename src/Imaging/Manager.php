<?php

namespace Statamic\Imaging;

use Statamic\Contracts\Imaging\ImageManipulator;
use Statamic\Facades\Glide;
use Statamic\Support\Arr;

class Manager
{
    /**
     * Get a URL manipulator instance to continue chaining, or a URL right away if provided with params.
     *
     * @param  null|string  $item  An asset, asset ID, /path, or http://external-url
     * @param  null|array  $params  Array of manipulation parameters.
     * @return string|ImageManipulator
     */
    public function manipulate($item = null, $params = null)
    {
        $manipulator = self::manipulator();

        if (! $item) {
            return $manipulator;
        }

        $manipulator->item($item);

        if ($params) {
            return $manipulator->params($params)->build();
        }

        return $manipulator;
    }

    /**
     * Get an image manipulator instance.
     *
     * @return ImageManipulator
     */
    public function manipulator()
    {
        return app(ImageManipulator::class);
    }

    /**
     * Get the image manipulation presets.
     *
     * @return array
     */
    public function manipulationPresets()
    {
        $presets = $this->userManipulationPresets();

        if (config('statamic.cp.enabled')) {
            $presets = array_merge($presets, $this->cpManipulationPresets());
        }

        return $presets;
    }

    /**
     * Get the user defined image manipulation presets.
     *
     * @return array
     */
    public function userManipulationPresets()
    {
        return collect(config('statamic.assets.image_manipulation.presets', []))
            ->map(function ($preset) {
                return $this->normalizePreset($preset);
            })
            ->all();
    }

    /**
     * Get the image manipulation presets used by the control panel.
     *
     * @return array
     */
    public function cpManipulationPresets()
    {
        $presets = array_merge(
            ['small' => 400],
            config('statamic.cp.thumbnail_presets', [])
        );

        return collect($presets)
            ->flatMap(fn ($size, $name) => [
                "cp_thumbnail_{$name}_landscape" => ['w' => $size, 'h' => $size, 'fit' => 'contain'],
                "cp_thumbnail_{$name}_portrait" => ['h' => $size, 'fit' => 'contain'],
                "cp_thumbnail_{$name}_square" => ['w' => $size, 'h' => $size],
            ])
            ->all();
    }

    /**
     * Normalize preset.
     *
     * @param  array  $preset
     * @return array
     */
    protected function normalizePreset($preset)
    {
        // When explicitly setting `crop_focal` in a preset, it breaks cropping
        // altogether. Statamic's glide tag will automatically respect focal
        // points though, so we can remove this parameter from the preset.
        if (Arr::get($preset, 'fit') === 'crop_focal') {
            Arr::forget($preset, 'fit');
        }

        return Glide::normalizeParameters($preset);
    }
}
