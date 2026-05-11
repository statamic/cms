<?php

namespace Statamic\Contracts\GraphQL;

interface CastableToValidationString
{
    public function toGqlValidationString(): string;
}
