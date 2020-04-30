<?php

namespace Statamic\Imaging;

use Statamic\Contracts\Imaging\ImageManipulator;

class Manager
{
    /**
     * Get a URL manipulator instance to continue chaining, or a URL right away if provided with params.
     *
     * @param null|string $item   An asset, asset ID, /path, or http://external-url
     * @param null|array  $params Array of manipulation parameters.
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
     * Get the image manipulation presets required by the control panel.
     *
     * @return array
     */
    public function getCpImageManipulationPresets()
    {
        return [
            'cp_thumbnail_small' => ['w' => '300', 'fit' => 'crop'],
            'cp_thumbnail_small_landscape' => ['w' => '400', 'h' => '300', 'fit' => 'crop'],
            'cp_thumbnail_small_portrait' => ['h' => '300', 'fit' => 'crop'],
            'cp_thumbnail_small_square' => ['w' => '300', 'h' => '300'],
            'cp_thumbnail_large' => ['w' => '1000', 'h' => '1000'],
        ];
    }
}
