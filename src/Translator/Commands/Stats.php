<?php

namespace Statamic\Translator\Commands;

use Illuminate\Filesystem\Filesystem;
use Statamic\Support\Str;
use Statamic\Translator\MethodDiscovery;
use Statamic\Translator\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Stats extends Command
{
    protected $discovery;
    protected $ignored;

    public function __construct(MethodDiscovery $discovery, array $ignored)
    {
        $this->discovery = $discovery;
        $this->ignored = $ignored;
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

        $strings = $strings->reject(function ($string) {
            return Str::startsWith($string, $this->ignored);
        });

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
                $isKey = Util::isKey($item['string']);

                return $type === 'key' ? $isKey : ! $isKey;
            });
        }

        $rows = $rows->sortBy(function ($item) use ($input) {
            return ($input->getOption('sort') === 'usages')
                ? [$item['usages'], strtolower($item['string'])]
                : strtolower($item['string']);
        });

        if ($filter = $input->getOption('filter')) {
            $rows = $rows->filter(function ($item) use ($filter) {
                return Str::contains(strtolower($item['string']), strtolower($filter));
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

        return 0;
    }

    protected function discover()
    {
        $dir = getcwd();
        $paths = [$dir.'/src', $dir.'/resources'];
        $discovery = new MethodDiscovery(new Filesystem, $paths);

        return $discovery->discover();
    }
}
