<?php

namespace Statamic\Addons\Taxonomy;

use Statamic\API\Str;
use Statamic\API\Term;
use Statamic\API\Helper;
use Statamic\API\Taxonomy;
use Statamic\Addons\Relate\RelateFieldtype;

class TaxonomyFieldtype extends RelateFieldtype
{
    /**
     * Pre-process the fieldtype
     *
     * Take the data from the YAML and convert it to a format ready for the Vue component.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        $data = Helper::ensureArray($data);

        foreach ($data as $key => $term) {
            // If the term contains a slash, it must already be a term ID, not just
            // a value or a slug. In this case, we can just leave it as-is.
            if (str_contains($term, '/')) {
                continue;
            }

            // For instances where a user manually types a term into a file, it may not match
            // the normalized version. This is perfectly functional, however the fieldtype
            // won't be able to match it to the list of values. We'll normalize them now.
            $term = Term::normalizeSlug($term);

            // The component is expecting an array of IDs. We'll turn the term into an ID
            // by sticking the field name on the front, which is also the taxonomy handle.
            $data[$key] = $this->getName() . '/' . $term;
        }

        return parent::preProcess($data);
    }

    /**
     * Process the data
     *
     * Take what came from the Vue component and return it in a format ready for YAML.
     *
     * @param mixed $data
     * @return mixed
     */
    public function process($data)
    {
        if (! $data) {
            return $data;
        }

        return parent::process(
            $this->normalizeAndCreateTerms($data)
        );
    }

    /**
     * Normalize and create terms
     *
     * Taxonomy fields get slugs saved.
     * Relational fields get IDs saved.
     * Newly added terms should get created.
     *
     * @param array $data
     * @return mixed
     */
    private function normalizeAndCreateTerms($data)
    {
        // If this field name is not a taxonomy, that means it's just a regular relational field.
        $isTaxonomyField = Taxonomy::handleExists($this->getName());

        // In a taxonomy field, the field name is the taxonomy handle.
        // Otherwise, the taxonomy will be specified as a config value.
        $taxonomy = ($isTaxonomyField) ? $this->getName() : $this->getFieldConfig('taxonomy');

        foreach (Helper::ensureArray($data) as $key => $value) {
            $data[$key] = ($isTaxonomyField)
                ? $this->normalizeTermInTaxonomyField($value, $taxonomy)
                : $this->normalizeTermInRelationalField($value, $taxonomy);
        }

        return $data;
    }

    /**
     * Normalize a term value from a taxonomy (not relational) field
     *
     * @param string $value
     * @param string $taxonomy
     * @return string
     */
    private function normalizeTermInTaxonomyField($value, $taxonomy)
    {
        // If the string contains a slash, it's already an ID. We just want the slug.
        // @todo Handle values with slashes that aren't IDs. Sometimes people just want a slash in a value.
        if (Str::contains($value, '/')) {
            return explode('/', $value)[1];
        }

        return $this->createTerm($value, $taxonomy);
    }

    /**
     * Normalize a term value from a relational (not taxonomy) field
     *
     * @param string $value
     * @param string $taxonomy
     * @return string
     */
    private function normalizeTermInRelationalField($value, $taxonomy)
    {
        // If the value contains a slash, it's probably already a term ID.
        // @todo Handle values with slashes that aren't IDs. Sometimes people just want a slash in a value.
        if (Str::contains($value, '/')) {
            return $value;
        }

        $slug = $this->createTerm($value, $taxonomy);

        return $taxonomy . '/' . $slug;
    }

    /**
     * Create the term, if one needs to be created, and return a normalized slug.
     *
     * @param string $value
     * @param string $taxonomy
     * @return string
     */
    private function createTerm($value, $taxonomy)
    {
        $slug = Term::normalizeSlug($value);

        // If the normalized slug is different than what was entered, we'll create a term object
        // where the title is what was entered. This way, the normalized slug can be used in
        // the YAML, but the value that was entered by the content author can be maintained.
        if ($value !== $slug) {
            Term::create($slug)->taxonomy($taxonomy)->with(['title' => $value])->save();
        }

        return $slug;
    }
}
