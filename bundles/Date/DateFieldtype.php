<?php

namespace Statamic\Addons\Date;

use Carbon\Carbon;
use Statamic\Extend\Fieldtype;

class DateFieldtype extends Fieldtype
{
    public function preProcess($data)
    {
        if (! $data) {
            return;
        }

        return Carbon::createFromFormat($this->dateFormat($data), $data)->format('Y-m-d H:i');
    }

    public function process($data)
    {
        $date = Carbon::parse($data);

        return $date->format($this->dateFormat($data));
    }

    private function dateFormat($date)
    {
        return $this->getFieldConfig(
            'format',
            strlen($date) > 10 ? 'Y-m-d H:i' : 'Y-m-d'
        );
    }
}
