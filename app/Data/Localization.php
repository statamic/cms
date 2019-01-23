<?php

namespace Statamic\Data;

use Statamic\API\Site;

trait Localization
{
    protected $id;
    protected $locale;
    protected $localizable;
    protected $shouldPropagate = true;

    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->id;
        }

        $this->id = $id;

        return $this;
    }

    public function locale($locale = null)
    {
        if (is_null($locale)) {
            return $this->locale;
        }

        $this->locale = $locale;

        return $this;
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    abstract public function sites();

    public function localizable($localizable = null)
    {
        if (is_null($localizable)) {
            return $this->localizable;
        }

        $this->localizable = $localizable;

        return $this;
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
