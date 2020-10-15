<?php

namespace Statamic\Data;

use Illuminate\Support\Facades\Storage;

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
            $livePreviewCache = json_decode(Storage::disk('local')->get('live-preview-cache.json'), true);

            if (isset($livePreviewCache[request()->get('preview')][$this->id()])) {
                $this->data($livePreviewCache[request()->get('preview')][$this->id()]);
            }
        }

        return $this;
    }
}
