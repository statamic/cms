<?php

namespace Statamic\Translator\Commands;

use Google\Cloud\Translate\V2\TranslateClient;
use Illuminate\Filesystem\Filesystem;
use Statamic\Support\Arr;
use Statamic\Translator\Placeholders;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarExporter\VarExporter;

class Translate extends Command
{
    protected $files;
    protected $excluded;
    protected $englishTranslations = [];
    protected $input;
    protected $output;
    protected $client;

    public function __construct(Filesystem $files, array $excluded)
    {
        $this->files = $files;
        $this->excluded = $excluded;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('translate')
            ->addArgument('lang', InputArgument::OPTIONAL, 'A comma delimited list of language codes to translate.', implode(',', $this->existingLanguages()))
            ->addOption('key', null, InputOption::VALUE_REQUIRED, 'Google Translate API Key');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->client = $this->createClient();

        foreach ($this->languages() as $lang) {
            $this->translateFiles($lang);
        }

        return 0;
    }

    protected function translateFiles($lang)
    {
        $this->translateStringFile($lang);
        $this->translateKeyFiles($lang);
    }

    protected function translateStringFile($lang)
    {
        $path = "resources/lang/{$lang}.json";
        $fullPath = getcwd().'/'.$path;
        if (! $this->files->exists($fullPath)) {
            return;
        }

        $existing = $this->files->get($fullPath);
        $existingTranslations = json_decode($existing, true);

        $pendingTranslations = collect($existingTranslations)->filter(function ($string) {
            return $string == '';
        })->count();

        if ($pendingTranslations === 0) {
            $this->output->writeln("<comment>[!]</comment> No pending translations for <comment>$lang</comment>.");

            return;
        }

        $this->output->writeln("Translating $lang...");

        $bar = new ProgressBar($this->output, $pendingTranslations);

        $translations = collect($existingTranslations)
            ->mapWithKeys(function ($string, $english) use ($lang, $bar) {
                if ($string == '') {
                    // Only translate empty lines
                    $string = $this->translate((new Placeholders)->wrap($english), $lang);
                    $bar->advance();
                }

                return [$english => $string];
            })
            ->all();

        $bar->finish();
        $this->output->writeln('');

        $contents = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $this->files->put($fullPath, $contents);

        $this->output->writeln("<info>[✓] Translations for <comment>$lang</comment> merged into <comment>$path</comment></info>");
    }

    protected function translateKeyFiles($lang)
    {
        collect($this->files->files(getcwd().'/resources/lang/'.$lang))
            ->filter(function ($file) {
                $filename = substr($file->getBasename(), 0, -4); // without extension

                return ! in_array($filename, $this->excluded);
            })
            ->each(function ($file) use ($lang) {
                $this->translateKeyFile($file, $lang);
            });
    }

    protected function translateKeyFile($file, $lang)
    {
        $fullPath = $file->getPathname();
        $filename = substr($file->getBasename(), 0, -4); // without extension
        $existing = Arr::dot(include $fullPath);
        $path = "resources/lang/{$lang}/{$filename}.php";

        $pendingTranslations = collect($existing)->filter(function ($string) {
            return $string == '';
        })->count();

        if ($pendingTranslations === 0) {
            $this->output->writeln("<comment>[!]</comment> No pending translations for <comment>$lang/$filename</comment>.");

            return;
        }

        $this->output->writeln("Translating $lang/$filename...");

        $bar = new ProgressBar($this->output, $pendingTranslations);

        $translations = collect($existing)
            ->mapWithKeys(function ($string, $key) use ($filename, $lang, $bar) {
                if ($string == '') {
                    // Only translate empty lines.
                    $english = $this->getEnglishTranslation($filename, $key);
                    $string = $english == '' ? '' : $this->translate($english, $lang);
                    $bar->advance();
                }

                return [$key => $string];
            })
            ->all();

        $bar->finish();
        $this->output->writeln('');

        $contents = "<?php\n\nreturn ".VarExporter::export($translations).";\n";

        $this->files->put($fullPath, $contents);

        $this->output->writeln("<info>[✓] Translations for <comment>$lang/$filename</comment> merged into <comment>$path</comment></info>");
    }

    protected function getEnglishTranslation($file, $key)
    {
        $translation = Arr::get($this->getEnglishTranslations($file), $key);

        return (new Placeholders)->wrap($translation);
    }

    protected function getEnglishTranslations($file)
    {
        if ($cached = $this->englishTranslations[$file] ?? null) {
            return $cached;
        }

        $path = getcwd()."/resources/lang/en/{$file}.php";

        return $this->englishTranslations[$file] = require $path;
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
            ->flip()->forget('en')->flip()
            ->all();
    }

    protected function createClient()
    {
        if (! $key = $this->input->getOption('key')) {
            throw new \Exception('An API key is required. Provide it using --key=API_KEY');
        }

        return new TranslateClient([
            'key' => $key,
            'retries' => 0,
        ]);
    }

    protected function translate($string, $lang)
    {
        $lang = explode('-', str_replace('_', '-', $lang))[0];

        $response = $this->client->translate($string, ['target' => $lang]);

        $translation = (new Placeholders)->unwrap($response['text']);

        return str_replace('&#39;', "'", $translation);
    }
}
