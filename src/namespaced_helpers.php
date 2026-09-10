<?php

namespace Statamic;

function trans($key, $replace = [], $locale = null)
{
    return Statamic::trans($key, $replace, $locale);
}

function trans_choice($key, $number, $replace = [], $locale = null)
{
    return Statamic::transChoice($key, $number, $replace, $locale);
}
