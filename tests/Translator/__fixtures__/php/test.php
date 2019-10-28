<?php

class Test
{
    public function method()
    {
        return [
            __('php underscore string'),
            __('php underscore :param', ['param']),
        ];
    }
}