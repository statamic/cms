<?php

namespace Statamic\Http\Resources\CP\Taxonomies;

use Illuminate\Pagination\AbstractPaginator;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Terms as TermsFieldtype;

class TermsFieldtypeTerms extends Terms
{
    private TermsFieldtype $fieldtype;
    public $collects = TermsFieldtypeListedTerm::class;

    public function __construct($resource, TermsFieldtype $fieldtype)
    {
        $this->fieldtype = $fieldtype;

        parent::__construct($resource);
    }

    protected function collectResource($resource)
    {
        $collection = parent::collectResource($resource);

        if ($collection instanceof AbstractPaginator) {
            $collection->getCollection()->each->fieldtype($this->fieldtype);
        } else {
            $collection->each->fieldtype($this->fieldtype);
        }

        return $collection;
    }

    protected function setColumns()
    {
        parent::setColumns();

        $columns = $this->columns;

        $type = Column::make('type')
            ->label(__('Taxonomy'))
            ->listable(true)
            ->defaultVisibility(true)
            ->visible(true)
            ->sortable(false)
            ->defaultOrder($columns->count() + 1);

        $columns->put('type', $type);

        $this->columns = $columns->rejectUnlisted()->values();
    }
}
