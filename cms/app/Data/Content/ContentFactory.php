<?php

namespace Statamic\Data\Content;

use Statamic\Contracts\Data\Content\ContentFactory as ContentFactoryContract;

abstract class ContentFactory implements ContentFactoryContract
{
    protected $data = [];
    protected $path;
    protected $published = true;
    protected $order;
    protected $id;

    /**
     * @param array $data
     * @return $this
     */
    public function with(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param bool $published
     * @return $this
     */
    public function published($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @param mixed $order
     * @return $this
     */
    public function order($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @param string|null $id
     * @return $this
     */
    public function id($id = null)
    {
        if (is_null($id)) {
            $this->id = true;
        } else {
            $this->id = $id;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function ensureId()
    {
        $this->id = true;

        return $this;
    }

    /**
     * @return mixed
     */
    public function save()
    {
        return $this->get()->save();
    }

    /**
     * @param $content
     * @return mixed
     */
    protected function identify($content)
    {
        if ($this->id === true) {
            $content->ensureId();
        } elseif ($this->id) {
            $content->id($this->id);
        }

        return $content;
    }
}
