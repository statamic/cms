<?php

namespace Statamic\Translator\Commands;

use Illuminate\Filesystem\Filesystem;
use Statamic\Support\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\VarExporter\VarExporter;

class Review extends Command
{
    protected $files;
    protected $englishTranslations = [];
    protected $input;
    protected $output;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('review')
            ->addArgument('lang', InputArgument::REQUIRED)
            ->addArgument('file', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $output->writeln('<info>The translation file will be updated as you answer each question.</info>');
        $output->writeln('');

        $lang = $input->getArgument('lang');

        if ($file = $input->getArgument('file')) {
            $this->reviewKeyFile($lang, $file);
        } else {
            $this->reviewStringFile($lang);
        }

        return 0;
    }

    protected function reviewStringFile($lang)
    {
        $path = "resources/lang/$lang.json";
        $fullPath = getcwd().'/'.$path;

        throw_if(! $this->files->exists($fullPath), new \Exception("$path does not exist."));

        $contents = $this->files->get($fullPath);
        $json = json_decode($contents, true);

        $helper = $this->getHelper('question');

        foreach ($json as $english => $translation) {
            $text = <<<EOL
en: <comment>$english</comment>
$lang: <comment>$translation</comment>
Enter new translation or hit enter to continue:

EOL;
            $question = new Question($text);
            $replacement = $helper->ask($this->input, $this->output, $question);
            if ($replacement == null) {
                continue;
            }
            if ($replacement != $translation) {
                $json[$english] = $replacement;
                $this->files->put($fullPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
            $this->output->writeln('');
        }
    }

    protected function reviewKeyFile($lang, $file)
    {
        $path = "resources/lang/$lang/$file.php";
        $fullPath = getcwd().'/'.$path;

        throw_if(! $this->files->exists($fullPath), new \Exception("$path does not exist."));

        $translations = Arr::dot(require $fullPath);

        $helper = $this->getHelper('question');

        foreach ($translations as $key => $translation) {
            $english = $this->getEnglishTranslation($file, $key);
            $text = <<<EOL
$key
en: <comment>$english</comment>
$lang: <comment>$translation</comment>
Enter new translation or hit enter to continue:

EOL;
            $question = new Question($text);
            $replacement = $helper->ask($this->input, $this->output, $question);
            if ($replacement == null) {
                continue;
            }
            if ($replacement != $translation) {
                $translations[$key] = $replacement;
                $contents = "<?php\n\nreturn ".VarExporter::export(Arr::undot($translations)).";\n";
                $this->files->put($fullPath, $contents);
            }
            $this->output->writeln('');
        }
    }

    protected function getEnglishTranslation($file, $key)
    {
        return Arr::get($this->getEnglishTranslations($file), $key);
    }

    protected function getEnglishTranslations($file)
    {
        if ($cached = $this->englishTranslations[$file] ?? null) {
            return $cached;
        }

        $path = getcwd()."/resources/lang/en/{$file}.php";

        return $this->englishTranslations[$file] = require $path;
    }
}
