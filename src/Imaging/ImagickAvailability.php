<?php

namespace Statamic\Imaging;

class ImagickAvailability
{
    public function available(): bool
    {
        return extension_loaded('imagick');
    }
}
