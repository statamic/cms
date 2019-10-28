<?php

class Test
{
    public function method()
    {
        return [
            __('php underscore single quote string'),
            __('php underscore single quote :param', ['param']),
            __("php underscore double quote string"),
            __("php underscore double quote :param", ['param']),
        ];
    }
}