<?php

namespace Statamic\Search;

use Statamic\API\Config;
use Partyline as Console;
use Statamic\Search\Comb\Index as Comb;
use Statamic\Search\Algolia\Index as Algolia;

abstract class Index
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ItemResolver
     */
    protected $itemResolver;

    public function __construct(ItemResolver $itemResolver)
    {
        $this->itemResolver = $itemResolver->setIndex($this);
    }

    /**
     * Get the index name.
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Set the index name.
     *
     * @param string $name
     * @return Index
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Factory method for making Index instances.
     *
     * @param string $name
     * @param string|null $driver
     * @return Index
     */
    public static function make($name, $driver = null)
    {
        if (! $driver) {
            $driver = Config::get('search.driver');
        }

        switch ($driver) {
            case 'algolia':
                $index = app(Algolia::class);
                break;

            default:
                $index = app(Comb::class);
        }

        return $index->setName($name);
    }

    /**
     * Insert a document into the index.
     *
     * @param string $id
     * @param array $fields
     */
    abstract public function insert($id, $fields);

    /**
     * Insert multiple documents into the index.
     *
     * @param array $documents  Array of documents, keyed by their ids.
     */
    abstract public function insertMultiple($documents);

    /**
     * Delete a document from the index.
     *
     * @param string $id
     */
    abstract public function delete($id);

    /**
     * Perform a search.
     *
     * @param string $query  The search term.
     * @param array $fields  Restrict the search to these fields.
     * @return \Illuminate\Support\Collection
     */
    abstract public function search($query, $fields = null);

    /**
     * Delete the entire index.
     *
     * @return void
     */
    abstract public function deleteIndex();

    /**
     * Whether the index exists.
     *
     * @return bool
     */
    abstract public function exists();

    public function update()
    {
        $this->deleteIndex();

        $fields = $this->itemResolver->getFields();

        $documents = [];
        $items = $this->itemResolver->getItems();

        $bar = Console::getOutput()->createProgressBar($items->count());

        foreach ($items as $id => $item) {
            $documents[$id] = $item->toSearchableArray($fields);
            $bar->advance();
        }

        $this->insertMultiple($documents);

        $bar->finish();
        Console::getOutput()->newLine();
        Console::checkInfo("Index {$this->name()} updated.");
        Console::getOutput()->newLine();

        return $this;
    }
}
