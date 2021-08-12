<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\Form;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\FormType;

class FormsQuery extends Query
{
    protected $attributes = [
        'name' => 'forms',
    ];

    public function type(): Type
    {
        return GraphQL::listOf(GraphQL::type(FormType::NAME));
    }

    public function resolve($root, $args)
    {
        return Form::all();
    }
}
