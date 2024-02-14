<?php

declare(strict_types = 1);
namespace Rebing\GraphQL\Error;

use GraphQL\Error\Error;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Contracts\Validation\Validator;

class ValidationError extends Error implements ProvidesErrorCategory
{
    /** @var Validator */
    private $validator;

    public function __construct(string $message, Validator $validator)
    {
        parent::__construct($message);

        $this->validator = $validator;
    }

    public function getValidatorMessages(): MessageBag
    {
        return $this->validator->errors();
    }

    public function getValidator(): Validator
    {
        return $this->validator;
    }

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'validation';
    }
}
