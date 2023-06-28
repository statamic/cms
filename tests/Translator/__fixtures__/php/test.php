<?php

class test
{
    public function method()
    {
        return [
            __('php underscore single quote string'),
            __('php underscore single quote :param', ['param']),
            __('php underscore double quote string'),
            __('php underscore double quote :param', ['param']),

            trans('php trans single quote string'),
            trans('php trans single quote :param', ['param']),
            trans('php trans double quote string'),
            trans('php trans double quote :param', ['param']),

            trans_choice('php trans_choice single quote string', 2),
            trans_choice('php trans_choice single quote :param', 2, ['param']),
            trans_choice('php trans_choice double quote string', 2),
            trans_choice('php trans_choice double quote :param', 2, ['param']),

            __('php with/slash'),
        ];
    }

    public function returnMethodOne()
    {
        /** @translation */
        return 'php annotated return single quote string';
    }

    public function returnMethodTwo()
    {
        /** @translation */
        return 'php annotated return single quote :param';
    }

    public function returnMethodThree()
    {
        /** @translation */
        return 'php annotated return double quote string';
    }

    public function returnMethodFour()
    {
        /** @translation */
        return 'php annotated return double quote :param';
    }

    public function returnMethodFive()
    {
        /* @translation */
        return 'php annotated return with single asterisk';
    }

    public function returnMethodSix()
    {
        /** @translation */
        return 'php annotated return with/slash';
    }
}
