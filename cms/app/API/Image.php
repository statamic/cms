<?php

namespace Statamic\API;

use Statamic\Contracts\Imaging\ImageManipulator;

class Image
{
    /**
     * Get a URL manipulator instance to continue chaining, or a URL right away if provided with params.
     *
     * @param null|string $item   An asset, asset ID, /path, or http://external-url
     * @param null|array  $params Array of manipulation parameters.
     * @return string|ImageManipulator
     */
    public static function manipulate($item = null, $params = null)
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
     * Get an image manipulator instance
     *
     * @return ImageManipulator
     */
    public static function manipulator()
    {
        return app(ImageManipulator::class);
    }

    /**
     * Get the image manipulation presets required by the control panel
     *
     * @return array
     */
    public static function getCpImageManipulationPresets()
    {
        return [
            'cp_thumbnail_small' => ['w' => '300', 'fit' => 'crop'],
            'cp_thumbnail_large' => ['w' => '1000', 'h' => '1000']
        ];
    }
}
