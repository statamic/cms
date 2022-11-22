<?php

namespace Statamic\Actions;

use Illuminate\Support\Str;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Facades\Term as TermAPI;

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

    public function visibleToBulk($items)
    {
        return $this->visibleTo($items->first());
    }

    public function run($items, $values)
    {
        collect($items)
            ->each(function ($item) {
                $itemTitleAndSlug = $this->generateTitleAndSlug($item);

                $term = TermAPI::make()
                    ->taxonomy($item->taxonomy())
                    ->blueprint($item->blueprint()->handle())
                    ->slug($itemTitleAndSlug['slug'])
                    ->data(
                        $item->data()
                            ->except($item->blueprint()->fields()->all()->reject->shouldBeDuplicated()->keys())
                            ->merge([
                                'title' => $itemTitleAndSlug['title'],
                                'duplicated_from' => $item->id(),
                            ])
                            ->toArray()
                    );

                $term->save();
            });
    }

    // This method has been copied over from Statamic v2 - it's been updated to work with v3.
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
        if (TermAPI::findBySlug($slug, $term->taxonomy()->handle())) {
            $generate = $this->generateTitleAndSlug($term, $attempt + 1);

            $title = $generate['title'];
            $slug = $generate['slug'];
        }

        return [
            'title' => $title,
            'slug' => $slug,
        ];
    }
}
