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
    protected $discovery;

    public function __construct(MethodDiscovery $discovery)
    {
        $this->discovery = $discovery;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('generate')
            ->addArgument('lang', InputArgument::OPTIONAL, 'A comma delimited list of language codes to generate.', 'en,fr,de');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $json = $this->discovery->discover()
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
}