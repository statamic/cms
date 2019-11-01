<?php

namespace Statamic\Translator\Commands;

use Illuminate\Filesystem\Filesystem;
use Statamic\Support\Str;
use Statamic\Translator\MethodDiscovery;
use Statamic\Translator\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarExporter\VarExporter;

class Generate extends Command
{
    protected $discovery;
    protected $discovered;
    protected $files;

    public function __construct(MethodDiscovery $discovery, Filesystem $files)
    {
        $this->discovery = $discovery;
        $this->files = $files;
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
        $this->input = $input;
        $this->output = $output;

        $this->discovered = $this->discovery->discover();

        $this->generateStringFiles();
        $this->generateKeyFiles();
    }

    protected function generateStringFiles()
    {
        $strings = $this->discovered->filter(function ($string) {
            return Util::isString($string);
        })->sort();

        foreach ($this->languages() as $lang) {
            if ($lang !== 'en') {
                continue;
            }

            $this->generateStringFile($lang, $strings);
        }
    }

    protected function generateStringFile($lang, $strings)
    {
        $path = 'resources/lang/'.$lang.'.json';
        $fullPath = __DIR__.'/../../../'.$path;

        $exists = file_exists($fullPath);
        $existing = $exists ? json_decode($this->files->get($fullPath), true) : [];

        $json = $strings->mapWithKeys(function ($string) use ($existing) {
            return [$string => $existing[$string] ?? ''];
        })->toJson(JSON_PRETTY_PRINT);

        $this->files->put($fullPath, $json);
        $this->output->writeln($exists
            ? "<info>Translation file for <comment>$lang</comment> merged into <comment>$path</comment></info>"
            : "<info>Translation file for <comment>$lang</comment> created at <comment>$path</comment></info>"
        );
    }

    protected function generateKeyFiles()
    {
        $this->discovered
            ->filter(function ($string) {
                return Util::isKey($string);
            })
            ->map(function ($string) {
                [$file, $string] = explode('.', $string, 2);

                if (! str_contains($file, '::')) {
                    $file = 'statamic::'.$file;
                }

                if (! Str::startsWith($file, 'statamic::')) {
                    return null;
                }

                $file = explode('::', $file, 2)[1];

                return compact('file', 'string');
            })
            ->filter()
            ->groupBy('file')
            ->each(function ($items, $file) {
                foreach ($this->languages() as $lang) {
                    $this->generateKeyFile($lang, $file, $items->map->string);
                }
            });
    }

    protected function generateKeyFile($lang, $file, $strings)
    {
        $path = 'resources/lang/'.$lang.'/'.$file.'.php';
        $fullPath =__DIR__.'/../../../'.$path;

        $exists = file_exists($fullPath);
        $existing = $exists ? require $fullPath : [];

        $strings = $strings->sort()->values()
            ->mapWithKeys(function ($key) use ($existing) {
                $translation = $existing[$key] ?? '';
                return [$key => $translation];
            })->all();

        $contents = "<?php\n\nreturn " . VarExporter::export($strings) . ";\n";

        $this->files->makeDirectory(dirname($fullPath), 0755, true, true);
        $this->files->put($fullPath, $contents);

        $this->output->writeln($exists
            ? "<info>Translation file for <comment>$lang</comment> merged into <comment>$path</comment></info>"
            : "<info>Translation file for <comment>$lang</comment> created at <comment>$path</comment></info>"
        );
    }

    protected function languages()
    {
        return explode(',', $this->input->getArgument('lang'));
    }
}