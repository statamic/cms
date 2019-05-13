<?php

namespace Statamic\Data;

use Statamic\API\Site;
use Statamic\FluentlyGetsAndSets;

trait Localization
{
    use FluentlyGetsAndSets;

    protected $id;
    protected $locale;
    protected $localizable;
    protected $shouldPropagate = true;

    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')->args(func_get_args());
    }

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    abstract public function sites();

    public function localizable($localizable = null)
    {
        return $this->fluentlyGetOrSet('localizable')->args(func_get_args());
    }

    protected function unlocalizableData()
    {
        $data = $this->blueprint()->fields()
            ->addValues($this->data)
            ->unlocalizable()
            ->values();

        return array_except($data, ['slug', 'order', 'published']);
    }

    public function propagate()
    {
        collect($this->sites())
            ->diff($this->locale())
            ->each(function ($site) {
                $this->localizable()
                    ->inOrClone($site)
                    ->merge($this->unlocalizableData())
                    ->saveWithoutPropagating();
            });

        return $this;
    }

    public function saveWithoutPropagating()
    {
        $state = $this->shouldPropagate;

        $this->shouldPropagate = false;

        $return = $this->save();

        $this->shouldPropagate = $state;

        return $return;
    }
}
