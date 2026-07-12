<?php

namespace Statamic\Tags\Concerns;

use Statamic\Facades\Blink;
use Statamic\Tags\Chunks;

trait GetsQueryResults
{
    protected function results($query)
    {
        $this
            ->allowLegacyStylePaginationLimiting()
            ->preventIncompatiblePaginationParameters();

        if ($paginate = $this->params->int('paginate')) {
            return $this->paginatedResults($query, $paginate);
        }

        if ($limit = $this->params->int('limit')) {
            $query->limit($limit);
        }

        if ($offset = $this->params->int('offset')) {
            $query->offset($offset);
        }

        if ($chunk = $this->params->int('chunk')) {
            return $this->chunkedResults($query, $chunk);
        }

        return $query->get();
    }

    protected function allowLegacyStylePaginationLimiting()
    {
        if ($this->params->get('paginate') === true) {
            $this->params->put('paginate', $this->params->get('limit'));
            $this->params->forget('limit');
        }

        return $this;
    }

    protected function preventIncompatiblePaginationParameters()
    {
        if ($this->params->int('paginate') && $this->params->int('limit')) {
            throw new \Exception('Cannot use [paginate] integer in combination with [limit] param.');
        }

        if ($this->params->has('paginate') && $this->params->has('chunk')) {
            throw new \Exception('Cannot use [paginate] in combination with [chunk] param.');
        }

        return $this;
    }

    protected function paginatedResults($query, $perPage)
    {
        if ($offset = $this->params->get('offset')) {
            $this->queryPaginationFriendlyOffset($query, $offset);
        }

        $pageName = $this->params->get('page_name', 'page');
        $paginator = $query->paginate($perPage, ['*'], $pageName);

        Blink::put('tag-paginator', $paginator);

        return tap($paginator, function ($paginator) {
            $paginator->setCollection($paginator->getCollection()->values());
        });
    }

    protected function queryPaginationFriendlyOffset($query, $offset)
    {
        $offsetIds = (clone $query)
            ->limit($offset)
            ->get('id')
            ->map->id()
            ->all();

        $query->whereNotin('id', $offsetIds);
    }

    protected function chunkedResults($query, $chunkSize)
    {
        $results = Chunks::make();

        $query->chunk($chunkSize, fn ($chunk) => $results->push($chunk));

        return $results;
    }
}
