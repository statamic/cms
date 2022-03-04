<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\DescriptorHelper;

class ListCommand extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:list
        {namespace? : The namespace name}
        {--raw : To output raw command list}
        {--format=txt : The output format (txt, xml, json, or md)}
    ';

    protected $description = 'List the Statamic commands';

    protected $hidden = true;

    protected $hiddenInPlease = false;

    public function handle()
    {
        (new DescriptorHelper)->describe($this->output, $this->getApplication(), [
            'format' => $this->option('format'),
            'raw_text' => $this->option('raw'),
            'namespace' => $this->argument('namespace'),
        ]);
    }

    public function getApplication(): ?Application
    {
        $app = parent::getApplication();

        foreach ($app->all() as $command) {
            if (! in_array(RunsInPlease::class, class_uses($command))) {
                $command->setHidden(true);
            }
        }

        return $app;
    }
}
