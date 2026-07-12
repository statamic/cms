<?php

namespace Statamic\Exceptions;

class DuplicateIdException extends \Exception
{
    private $path;
    private $existing_path;
    private $existing_repo;
    private $item_id;

    /**
     * @param  string  $path
     * @param  string  $existing_path
     * @param  string  $repo
     * @param  string  $id
     */
    public function __construct($path, $existing_path, $repo, $id)
    {
        parent::__construct();

        $this->path = $path;
        $this->existing_path = $existing_path;
        $this->existing_repo = $repo;
        $this->item_id = $id;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getExistingPath()
    {
        return $this->existing_path;
    }

    public function getExistingRepo()
    {
        return $this->existing_repo;
    }

    public function getItemId()
    {
        return $this->item_id;
    }
}
