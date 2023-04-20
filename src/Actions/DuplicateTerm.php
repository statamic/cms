<?php

namespace Statamic\Actions;

use Illuminate\Support\Str;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Facades\Term as Terms;

class DuplicateTerm extends Action
{
    public static function title()
    {
        return __('Duplicate');
    }

    public function visibleTo($item)
    {
        return $item instanceof Term;
    }

    public function run($items, $values)
    {
        $items->each(function (Term $original) {
            [$title, $slug] = $this->generateTitleAndSlug($original);

            $data = $original->data()
                ->except($original->blueprint()->fields()->all()->reject->shouldBeDuplicated()->keys())
                ->merge([
                    'title' => $title,
                    'duplicated_from' => $original->id(),
                ])->all();

            $term = Terms::make()
                ->taxonomy($original->taxonomy())
                ->blueprint($original->blueprint()->handle())
                ->slug($slug)
                ->data($data);

            $term->save();
        });
    }

    protected function generateTitleAndSlug(Term $term, $attempt = 1)
    {
        $title = $term->get('title');
        $slug = $term->slug();
        $suffix = ' ('.__('Duplicated').')';

        if ($attempt == 1) {
            $title = $title.$suffix;
        }

        if ($attempt !== 1) {
            if (! Str::contains($title, $suffix)) {
                $title .= $suffix;
            }

            $title .= ' ('.$attempt.')';
        }

        $slug .= '-'.$attempt;

        // If the slug we've just built already exists, we'll try again, recursively.
        if ($term->taxonomy()->queryTerms()->where('slug', $slug)->count()) {
            [$title, $slug] = $this->generateTitleAndSlug($term, $attempt + 1);
        }

        return [$title, $slug];
    }

    public function authorize($user, $item)
    {
        return $user->can('create', [Term::class, $item->taxonomy()]);
    }
}
