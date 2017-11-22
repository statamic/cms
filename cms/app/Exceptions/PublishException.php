<?php

namespace Statamic\Exceptions;

class PublishException extends \Exception
{
    /**
     * @var arrays
     */
    protected $errors;

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
