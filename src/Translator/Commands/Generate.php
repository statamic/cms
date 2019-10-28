<?php

namespace Statamic\Translator\Commands;

use Illuminate\Filesystem\Filesystem;
use Statamic\Translator\MethodDiscovery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Generate extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate')
            ->addArgument('lang', InputArgument::OPTIONAL, 'A comma delimited list of language codes to generate.', 'en,fr,de');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $json = $this->discover()
            ->sort()
            ->mapWithKeys(function ($string) {
                return [$string => ''];
            })
            ->toJson(JSON_PRETTY_PRINT);

        $langs = explode(',', $input->getArgument('lang'));

        foreach ($langs as $lang) {
            if ($lang === 'en') {
                continue;
            }

            $path = 'resources/lang/'.$lang.'.json';
            (new Filesystem)->put(__DIR__.'/../../../'.$path, $json);
            $output->writeln("<info>Translation file for <comment>$lang</comment> written to <comment>$path</comment></info>");
        }
    }

    protected function discover()
    {
        $dir = getcwd();
        $paths = [$dir.'/src', $dir.'/resources'];
        $discovery = new MethodDiscovery(new Filesystem, $paths);
        return $discovery->discover();
    }
}