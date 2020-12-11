<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Value;
use Statamic\Support\Str;

class TermType extends \Rebing\GraphQL\Support\Type
{
    private $taxonomy;
    private $blueprint;

    public function __construct($taxonomy, $blueprint)
    {
        $this->taxonomy = $taxonomy;
        $this->blueprint = $blueprint;
        $this->attributes['name'] = static::buildName($taxonomy, $blueprint);
    }

    public static function buildName(Taxonomy $taxonomy, Blueprint $blueprint): string
    {
        return 'Term_'.Str::studly($taxonomy->handle()).'_'.Str::studly($blueprint->handle());
    }

    public function interfaces(): array
    {
        return [
            GraphQL::type(TermInterface::NAME),
        ];
    }

    public function fields(): array
    {
        return $this->blueprint->fields()->toGraphQL()
            ->merge((new TermInterface)->fields())
            ->map(function (array $arr) {
                $arr['resolve'] = $this->resolver();

                return $arr;
            })
            ->all();
    }

    private function resolver()
    {
        return function (Term $term, $args, $context, $info) {
            $value = $term->augmentedValue($info->fieldName);

            if ($value instanceof Value) {
                $value = $value->value();
            }

            return $value;
        };
    }
}
