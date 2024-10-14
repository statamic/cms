<?php

namespace Statamic\Contracts\Imaging;

use Statamic\Imaging\Manipulators\Sources\Source;

interface Manipulator
{
    /**
     * Sets the source image to be manipulated.
     */
    public function setSource(Source $source): self;

    /**
     * Adds manipulations to be performed.
     */
    public function addParams(array $params): self;

    /**
     * Adds the appropriate params that would control focal point cropping.
     */
    public function addFocalPointParams(float $x, float $y, float $z): self;

    /**
     * Gets the manipulations to be performed.
     */
    public function getParams(): array;

    /**
     * Gets the available manipulation parameters.
     */
    public function getAvailableParams(): array;

    /**
     * Gets the URL of the manipulated image.
     */
    public function getUrl(): string;

    /**
     * Reads the manipulated image and returns it as a data URL.
     */
    public function getDataUrl(): string;

    /**
     * Get attributes about the image (e.g. width, height)
     */
    public function getAttributes(): array;
}
