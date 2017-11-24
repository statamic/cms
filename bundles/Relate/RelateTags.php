<?php

namespace Statamic\Addons\Relate;

use Statamic\API\Data;
use Statamic\API\Str;
use Statamic\API\User;
use Statamic\API\Helper;
use Statamic\API\Content;
use Statamic\API\Pattern;
use Statamic\API\Term as TermAPI;
use Statamic\Contracts\Data\Taxonomies\Term;
use Statamic\Data\ContentCollection;
use Statamic\Addons\Collection\CollectionTags;

class RelateTags extends CollectionTags
{
    public function __call($method, $args)
    {
        $var = explode(':', $this->tag, 2)[1];

        $this->collection = collect_content();

        $values = Helper::ensureArray(array_get($this->context, $var, []));

        foreach ($values as $value) {
            $content = $this->getRelation($value);

            if (! $content) {
                continue;
            }

            $this->collection->push($content);
        }

        // Swap to the appropriate locale. By default it's the site locale.
        $this->collection = $this->collection->localize($this->get('locale', site_locale()));

        // Convert taxonomy fields to actual taxonomy terms.
        // This will allow taxonomy term data to be available in the template without additional tags.
        // If terms are not needed, there's a slight performance benefit in disabling this.
        if ($this->getBool('supplement_taxonomies', true)) {
            $this->collection = $this->collection->supplementTaxonomies();
        }

        $this->filter();

        if ($this->collection->isEmpty()) {
            return $this->parseNoResults();
        }

        return $this->output();
    }

    private function getRelation($value)
    {
        if ($value instanceof Term) {
            return $value;
        }

        // If the value is a term value (not a term id), the developer may add a taxonomy
        // parameter to provide a hint to which taxonomy the value is located within.
        if ($taxonomy = $this->get('taxonomy')) {
            return TermAPI::whereSlug(TermAPI::normalizeSlug($value), $taxonomy);
        }

        return Data::find($value);
    }

    protected function getSortOrder()
    {
        return $this->get('sort');
    }
}
