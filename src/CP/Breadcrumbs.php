<?php

namespace Statamic\CP;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class Breadcrumbs implements Arrayable, JsonSerializable
{
    protected $crumbs;

    public function __construct($crumbs)
    {
        $this->crumbs = collect($crumbs);
    }

    public static function make($crumbs)
    {
        return new static($crumbs);
    }

    public function toArray()
    {
        return $this->crumbs->toArray();
    }

    public function toJson()
    {
        return $this->crumbs->toJson();
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->crumbs->toArray();
    }

    public function title($title = null)
    {
        $new_crumbs = $this->crumbs->map(fn ($v) => ['text' => __($v['text']), 'url' => $v['url']]);
        $crumbs = $new_crumbs->map->text;

        if ($title) {
            $crumbs->push(__($title));
        }

        return $crumbs->reverse()->join(' ‹ ');
    }
}
