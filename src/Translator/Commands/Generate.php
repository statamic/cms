<?php

namespace Statamic\Translator\Commands;

use Illuminate\Filesystem\Filesystem;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Translator\MethodDiscovery;
use Statamic\Translator\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarExporter\VarExporter;

class Generate extends Command
{
    protected $discovery;
    protected $discovered;
    protected $files;
    protected $ignored;
    protected $additionalStrings;
    protected $additionalKeys;
    protected $excludedKeys;
    protected $manualFiles;
    protected $input;
    protected $output;

    public function __construct(MethodDiscovery $discovery, Filesystem $files, array $manualFiles, array $ignored, array $additionalStrings, array $additionalKeys, array $excludedKeys)
    {
        $this->discovery = $discovery;
        $this->files = $files;
        $this->manualFiles = $manualFiles;
        $this->ignored = $ignored;
        $this->additionalStrings = $additionalStrings;
        $this->additionalKeys = $additionalKeys;
        $this->excludedKeys = $excludedKeys;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('generate')
            ->addArgument('lang', InputArgument::OPTIONAL, 'A comma delimited list of language codes to generate.', implode(',', $this->existingLanguages()))
            ->addOption('translate', null, InputOption::VALUE_NONE, 'Whether to translate using Google Translate')
            ->addOption('key', null, InputOption::VALUE_REQUIRED, 'Google Translate API Key');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->discovered = $this->discovery->discover();

        $this->generateStringFiles();
        $this->generateKeyFiles();
        $this->generateManualKeyFiles();

        $this->translate();

        return 0;
    }

    protected function generateStringFiles()
    {
        $strings = $this->discovered->filter(function ($string) {
            return ! Str::startsWith($string, $this->ignored) && Util::isString($string);
        })->merge($this->additionalStrings)->sortBy(function ($string) {
            return strtolower($string);
        })->unique();

        foreach ($this->languages() as $lang) {
            if ($lang === 'en') {
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
        $existing = $exists ? json_decode($existingJson = $this->files->get($fullPath), true) : [];

        $json = $strings->mapWithKeys(function ($string) use ($existing) {
            return [$string => $existing[$string] ?? ''];
        })->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";

        if ($json === ($existingJson ?? null)) {
            $this->output->writeln("<comment>[!]</comment> Translation file for <comment>$lang</comment> not written because there are no changes.");

            return;
        }

        $this->files->put($fullPath, $json);
        $this->output->writeln($exists
            ? "<info>[✓] Translation file for <comment>$lang</comment> merged into <comment>$path</comment></info>"
            : "<info>[✓] Translation file for <comment>$lang</comment> created at <comment>$path</comment></info>"
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

                if (! Str::contains($file, '::')) {
                    $file = 'statamic::'.$file;
                }

                if (! Str::startsWith($file, 'statamic::')) {
                    return null;
                }

                $file = explode('::', $file, 2)[1];

                if (Str::startsWith($file, $this->manualFiles) || in_array($file, ['auth'])) {
                    return null;
                }

                return compact('file', 'string');
            })
            ->filter()
            ->groupBy('file')
            ->each(function ($items, $file) {
                foreach ($this->languages() as $lang) {
                    $keys = $items->map->string
                        ->merge($this->additionalKeys[$file] ?? [])
                        ->diff($this->excludedKeys[$file] ?? [])
                        ->sort()->values();
                    $this->generateKeyFile($lang, $file, function ($existing) use ($keys) {
                        return $keys->mapWithKeys(function ($key) use ($existing) {
                            $translation = $existing[$key] ?? '';

                            return [$key => $translation];
                        })->all();
                    });
                }
            });
    }

    protected function generateKeyFile($lang, $file, $translationCallback)
    {
        $path = 'resources/lang/'.$lang.'/'.$file.'.php';
        $fullPath = __DIR__.'/../../../'.$path;

        $exists = file_exists($fullPath);
        $existing = $exists ? require $fullPath : [];

        $translations = $translationCallback($existing);

        if ($translations === $existing) {
            $this->output->writeln("<comment>[!]</comment> Translation file for <comment>$lang/$file</comment> not written because there are no changes.");

            return;
        }

        $translations = Arr::dot($translations);

        $contents = "<?php\n\nreturn ".VarExporter::export($translations).";\n";

        $this->files->makeDirectory(dirname($fullPath), 0755, true, true);
        $this->files->put($fullPath, $contents);

        $this->output->writeln($exists
            ? "<info>[✓] Translation file for <comment>$lang/$file</comment> merged into <comment>$path</comment></info>"
            : "<info>[✓] Translation file for <comment>$lang/$file</comment> created at <comment>$path</comment></info>"
        );
    }

    protected function generateManualKeyFiles()
    {
        foreach ($this->manualFiles as $file) {
            $source = 'resources/lang/en/'.$file.'.php';
            $fullSourcePath = __DIR__.'/../../../'.$source;
            $strings = require $fullSourcePath;
            $strings = collect(Arr::dot($strings));

            foreach ($this->languages() as $lang) {
                if ($lang === 'en') {
                    continue;
                }

                $this->generateKeyFile($lang, $file, function ($existing) use ($strings) {
                    return $strings->mapWithKeys(function ($value, $key) use ($existing) {
                        return [$key => $this->getManualKeyFileTranslation($key, $value, $existing)];
                    })->all();
                });
            }
        }
    }

    private function getManualKeyFileTranslation($key, $value, $existing)
    {
        if (is_array($value)) {
            $existing = $existing[$key] ?? [];

            return collect($value)->map(function ($v, $k) use ($existing) {
                return $this->getManualKeyFileTranslation($k, $v, $existing);
            })->all();
        }

        return $existing[$key] ?? '';
    }

    protected function languages()
    {
        return explode(',', $this->input->getArgument('lang'));
    }

    protected function existingLanguages()
    {
        return collect($this->files->directories(getcwd().'/resources/lang'))
            ->map(function ($dir) {
                return basename($dir);
            })
            ->prepend('en')->unique()
            ->all();
    }

    protected function translate()
    {
        if (! $this->input->getOption('translate')) {
            $this->output->writeln('Run `php translator translate` to translate any missing lines using Google Translate.');

            return;
        }

        $this->getApplication()->find('translate')->run(new ArrayInput([
            'command' => 'translate',
            'lang' => $this->input->getArgument('lang'),
            '--key'  => $this->input->getOption('key'),
        ]), $this->output);
    }
}
