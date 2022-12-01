<?php

namespace Statamic\Assets;

use Statamic\Data\AbstractAugmented;
use Statamic\Modifiers\Modify;
use Statamic\Support\Str;

class AugmentedAsset extends AbstractAugmented
{
    public function keys()
    {
        $keys = $this->data->data()->keys()
            ->merge($this->data->supplements()->keys())
            ->merge([
                'id',
                'title',
                'path',
                'filename',
                'basename',
                'extension',
                'is_asset',
                'is_audio',
                'is_previewable',
                'is_image',
                'is_video',
                'blueprint',
                'edit_url',
                'container',
                'folder',
                'url',
                'permalink',
                'api_url',
            ]);

        if ($this->data->exists()) {
            $keys = $keys->merge([
                'size',
                'size_bytes',
                'size_kilobytes',
                'size_megabytes',
                'size_gigabytes',
                'size_b',
                'size_kb',
                'size_mb',
                'size_gb',
                'last_modified',
                'last_modified_timestamp',
                'last_modified_instance',
                'focus',
                'has_focus',
                'focus_css',
                'height',
                'width',
                'orientation',
                'ratio',
                'mime_type',
                'duration',
                'duration_seconds',
                'duration_minutes',
                'duration_sec',
                'duration_min',
                'playtime',
            ]);
        }

        return $keys->all();
    }

    protected function isAsset()
    {
        return true;
    }

    protected function permalink()
    {
        return $this->data->absoluteUrl();
    }

    protected function size()
    {
        return Str::fileSizeForHumans($this->sizeBytes());
    }

    protected function sizeBytes()
    {
        return $this->data->size();
    }

    protected function sizeB()
    {
        return $this->sizeBytes();
    }

    protected function sizeKilobytes()
    {
        return (float) number_format($this->sizeBytes() / 1024, 2);
    }

    protected function sizeKb()
    {
        return $this->sizeKilobytes();
    }

    protected function sizeMegabytes()
    {
        return (float) number_format($this->sizeBytes() / 1048576, 2);
    }

    protected function sizeMb()
    {
        return $this->sizeMegabytes();
    }

    protected function sizeGigabytes()
    {
        return (float) number_format($this->sizeBytes() / 1073741824, 2);
    }

    protected function sizeGb()
    {
        return $this->sizeGigabytes();
    }

    protected function lastModifiedTimestamp()
    {
        return $this->data->lastModified()->timestamp;
    }

    protected function lastModifiedInstance()
    {
        return $this->data->lastModified();
    }

    protected function focus()
    {
        return $this->data->get('focus', '50-50-1');
    }

    protected function hasFocus()
    {
        return $this->data->has('focus');
    }

    protected function focusCss()
    {
        return Modify::value($this->get('focus'))->backgroundPosition()->fetch();
    }

    protected function duration()
    {
        return round($this->data->duration() ?? 0);
    }

    protected function durationSeconds()
    {
        return $this->duration();
    }

    protected function durationSec()
    {
        return $this->duration();
    }

    protected function durationMinutes()
    {
        if (! $seconds = $this->duration()) {
            return null;
        }

        return floor($seconds / 60).':'.$seconds % 60;
    }

    protected function durationMin()
    {
        return $this->durationMinutes();
    }

    protected function playtime()
    {
        if ($this->duration() >= 3600) {
            return date('H:i:s', $this->duration());
        }

        return date('i:s', $this->duration());
    }
}
