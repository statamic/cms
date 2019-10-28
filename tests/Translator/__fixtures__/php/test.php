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

            trans('php trans single quote string'),
            trans('php trans single quote :param', ['param']),
            trans("php trans double quote string"),
            trans("php trans double quote :param", ['param']),

            trans_choice('php trans_choice single quote string', 2),
            trans_choice('php trans_choice single quote :param', 2, ['param']),
            trans_choice("php trans_choice double quote string", 2),
            trans_choice("php trans_choice double quote :param", 2, ['param']),
        ];
    }
}