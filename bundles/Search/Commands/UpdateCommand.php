<?php

namespace Statamic\Addons\Search\Commands;

use Statamic\API\Collection;
use Statamic\API\Config;
use Statamic\API\Content;
use Statamic\API\Entry;
use Statamic\API\Search;
use Statamic\API\Str;
use Statamic\Console\Commands\AbstractCommand;
use Statamic\Search\Index;

class UpdateCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:update { index? : The handle of the index to update. }
                                          { --i|interactive : Interactive. }
                                          { --all : Update all indexes. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the search index';

    /**
     * @var Index
     */
    protected $index;

    protected $defaultIndexName;

    public function __construct()
    {
        parent::__construct();

        $this->defaultIndexName = Config::get('search.default_index');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ($this->getIndexes() as $index) {
            $this->line("Updating <comment>{$index}</comment> index...");
            Search::in($index)->update();
        }
    }

    private function getIndexes()
    {
        if ($index = $this->argument('index')) {
            if (! $this->indexExists($index)) {
                $this->throwIndexNotExistingException($index);
            }
            return [$index];
        }

        if ($this->option('all')) {
            return $this->indexes();
        }

        if ($this->option('interactive')) {
            $choices = collect(['all'])->merge($this->indexes());

            $selection = $this->choice('Select an index to update', $choices->all());

            if ($selection == 'all') {
                return $this->indexes();
            }

            return [$selection];
        }

        return [$this->defaultIndexName];
    }

    private function indexes()
    {
        return Search::indexes()->keys();
    }

    private function indexExists($index)
    {
        return $this->indexes()->contains($index);
    }

    private function throwIndexNotExistingException($index)
    {
        $message = "Index [$index] does not exist.";

        if (Str::startsWith($index, 'collections/')) {
            list(, $handle) = explode('/', $index);

            if (Collection::handleExists($handle)) {
                $message = "Collection [$handle] is not searchable. Add searchable fields to enable searching.";
            }
        }

        throw new \Exception($message);
    }
}
