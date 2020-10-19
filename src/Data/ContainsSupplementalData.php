<?php

namespace Statamic\Data;

use Illuminate\Support\Facades\Cache;

trait ContainsSupplementalData
{
    protected $supplements;

    public function supplements()
    {
        return $this->supplements;
    }

    public function setSupplement($key, $value)
    {
        $this->supplements[$key] = $value;

        return $this;
    }

    public function getSupplement($key)
    {
        return $this->supplements[$key] ?? null;
    }

    public function forLivePreview()
    {
        if (request()->get('preview')) {
            $livePreviewCache = Cache::get('live-preview-data', []);

            if (isset($livePreviewCache[request()->get('preview')][$this->id()])) {
                $this->data($livePreviewCache[request()->get('preview')][$this->id()]);
            }
        }

        return $this;
    }
}
