<?php

namespace Statamic\Eloquent\Entries;

use Statamic\API;
use Statamic\API\Collection;
use Statamic\Data\Entries\Entry as FileEntry;

class Entry extends FileEntry
{
    protected $model;

    public static function fromModel(Model $model)
    {
        return API\Entry::make()
            ->id($model->id)
            ->locale($model->site)
            ->slug($model->slug)
            ->date($model->date)
            ->collection(Collection::whereHandle($model->collection))
            ->data($model->data)
            ->published($model->published)
            ->model($model);
    }

    public function toModel()
    {
        return new Model([
            'origin_id' => $this->originId(),
            'site' => $this->locale(),
            'slug' => $this->slug(),
            'date' => $this->hasDate() ? $this->date() : null,
            'collection' => $this->collectionHandle(),
            'data' => $this->data(),
            'published' => $this->published(),
        ]);
    }

    public function model($model = null)
    {
        if (func_num_args() === 0) {
            return $this->model;
        }

        $this->model = $model;

        $this->id($model->id);

        return $this;
    }

    public function lastModified()
    {
        return $this->model->updated_at;
    }

    public function origin($origin = null)
    {
        if (func_num_args() > 0) {
            $this->origin = $origin;

            return $this;
        }

        if ($this->origin) {
            return $this->origin;
        }

        if (! $this->model->origin) {
            return null;
        }

        return self::fromModel($this->model->origin);
    }

    public function originId()
    {
        return optional($this->origin)->id() ?? $this->model->origin_id;
    }

    public function hasOrigin()
    {
        return $this->originId() !== null;
    }

    public function in($site = null)
    {
        if ($site === $this->locale()) {
            return $this;
        }

        return API\Entry::query()
            ->where('origin_id', $this->id())
            ->where('site', $site)
            ->first();
    }
}
