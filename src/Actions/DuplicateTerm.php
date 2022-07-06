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
                if ($item instanceof Term) {
                    $itemTitleAndSlug = $this->generateTitleAndSlug($item);

                    $term = TermAPI::make()
                        ->taxonomy($item->taxonomy())
                        ->blueprint($item->blueprint()->handle())
                        ->slug($itemTitleAndSlug['slug'])
                        ->data(
                            $item->data()
                                ->except(config("statamic.duplicator.ignored_fields.terms.{$item->taxonomyHandle()}"))
                                ->merge([
                                    'title' => $itemTitleAndSlug['title'],
                                ])
                                ->toArray()
                        );

                    if (config('statamic.duplicator.fingerprint') === true) {
                        $term->set('is_duplicate', true);
                    }

                    $term->save();
                }
            });
    }

    // This method has been copied over from Statamic v2 - it's been updated to work with v3.
    protected function generateTitleAndSlug(Term $term, $attempt = 1)
    {
        $title = $term->get('title');
        $slug = $term->slug();

        if ($attempt == 1) {
            $title = $title . __(' (Duplicated)');
        }

        if ($attempt !== 1) {
            if (! Str::contains($title, __(' (Duplicated)'))) {
                $title .= __(' (Duplicated)');
            }

            $title .= ' (' . $attempt . ')';
        }

        $slug .= '-' . $attempt;

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
