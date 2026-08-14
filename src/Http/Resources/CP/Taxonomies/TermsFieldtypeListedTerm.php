<?php

namespace Statamic\Http\Resources\CP\Taxonomies;

use Statamic\Fieldtypes\Terms as TermsFieldtype;

class TermsFieldtypeListedTerm extends ListedTerm
{
    private TermsFieldtype $fieldtype;

    public function fieldtype(TermsFieldtype $fieldtype): self
    {
        $this->fieldtype = $fieldtype;

        return $this;
    }

    public function toArray($request)
    {
        $arr = array_merge(parent::toArray($request), $this->fieldtype->itemHierarchyMeta($this->resource));

        if ($hint = $this->fieldtype->getItemHint($this->resource)) {
            $arr['hint'] = $hint;
        }

        return $arr;
    }
}
