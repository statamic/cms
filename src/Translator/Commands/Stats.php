<?php

namespace Statamic\Translator\Commands;

use Illuminate\Filesystem\Filesystem;
use Statamic\Translator\MethodDiscovery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Stats extends Command
{
    protected $discovery;

    public function __construct(MethodDiscovery $discovery)
    {
        $this->discovery = $discovery;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('stats')
            ->addOption('sort', null, InputOption::VALUE_OPTIONAL, 'Sort method. string or usages', 'string')
            ->addOption('filter', null, InputOption::VALUE_OPTIONAL, 'Filter by string')
            ->addOption('min-words', null, InputOption::VALUE_OPTIONAL, 'Filter by strings with at least this many words')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Either "string" or "key"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $counts = collect();

        $strings = $this->discovery->discover();

        foreach ($strings as $string) {
            $count = $counts[$string] ?? 0;
            $counts[$string] = ++$count;
        }

        $rows = $counts->map(function ($count, $string) {
            return [
                'string' => $string,
                'usages' => $count,
            ];
        });

        if ($type = $input->getOption('type')) {
            if (! in_array($type, ['key', 'string'])) {
                throw new \LogicException('Invalid type. Allowed: "key" or "string"');
            }
            $rows = $rows->filter(function ($item) use ($type) {
                $isKey = $this->isKey($item['string']);;
                return $type === 'key' ? $isKey : !$isKey;
            });
        }

        $rows = $rows->sortBy(function ($item) use ($input) {
            return ($input->getOption('sort') === 'usages')
                ? [$item['usages'], strtolower($item['string'])]
                : strtolower($item['string']);
        });

        if ($filter = $input->getOption('filter')) {
            $rows = $rows->filter(function ($item) use ($filter) {
                return str_contains(strtolower($item['string']), strtolower($filter));
            });
        }

        if ($minWords = $input->getOption('min-words')) {
            $rows = $rows->filter(function ($item) use ($minWords) {
                return substr_count($item['string'], ' ') >= ($minWords - 1);
            });
        }

        $table = new Table($output);
        $table->setHeaders(['String', 'Usages']);
        $table->setRows($rows->values()->all());
        $table->render();
    }

    protected function discover()
    {
        $dir = getcwd();
        $paths = [$dir.'/src', $dir.'/resources'];
        $discovery = new MethodDiscovery(new Filesystem, $paths);
        return $discovery->discover();
    }

    protected function isKey($string)
    {
        // It's considered a translation key if:
        // - it has a dot (eg. "foo.bar")
        // - the dot is *not* at the end of the string (eg. "Hello.")
        // - there's not a space after the dot (eg. "No. Forking. Way.")
        // - there's not another dot after the dot. (eg. "What...")
        return preg_match('/\.(?![\.\s]).+/', $string);
    }
}