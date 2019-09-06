<?php

namespace Statamic\Yaml;

use Exception;
use Statamic\API\File;
use ReflectionProperty;
use Statamic\API\Pattern;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Statamic\YAML\ParseException as StatamicParseException;

class Yaml
{
    protected $file;

    public function file($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Parse a string of raw YAML into an array
     *
     * @param null|string $str  The YAML string
     * @return array
     */
    public function parse($str = null)
    {
        if (func_num_args() === 0) {
            throw_if(!$this->file, new Exception('Cannot parse YAML without a file or string.'));
            $str = File::get($this->file);
        }

        if (empty($str)) {
            return [];
        }

        if (Pattern::startsWith($str, '---')) {
            $split = preg_split("/\n---/", $str, 2, PREG_SPLIT_NO_EMPTY);
            $str = $split[0];
            $content = ltrim(array_get($split, 1, ''));
        }

        try {
            $yaml = SymfonyYaml::parse($str);
        } catch (\Exception $e) {
            throw $this->viewException($e, $str);
        };

        return isset($content)
            ? $yaml + ['content' => $content]
            : $yaml;
    }

    /**
     * Dump some YAML
     *
     * @param array        $data
     * @param string|bool  $content
     * @return string
     */
    public function dump($data, $content = false)
    {
        $yaml = SymfonyYaml::dump($data, 100, 2, SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        if ($content) {
            $fenced = "---".PHP_EOL . $yaml . "---".PHP_EOL;
            $yaml = $fenced . $content;
        }

        return $yaml ?: '';
    }

        /**
     * Dump some YAML
     *
     * @param array        $data
     * @param string|bool  $content
     * @return string
     */
    public function dumpFrontMatter($data, $content = '')
    {
        $yaml = SymfonyYaml::dump($data, 100, 2, SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        $yaml = "---".PHP_EOL . $yaml . "---".PHP_EOL . $content;

        return $yaml ?: '';
    }

    protected function viewException($e, $str)
    {
        $path = $this->file ?? $this->createTemporaryExceptionFile($str);

        $args = [
            $e->getMessage(), 0, 1, $path, $e->getParsedLine(), $e
        ];

        $exception = new StatamicParseException(...$args);

        $trace = $exception->getTrace();
        array_unshift($trace, [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'class' => StatamicParseException::class,
            'args' => $args,
        ]);
        $traceProperty = new ReflectionProperty('Exception', 'trace');
        $traceProperty->setAccessible(true);
        $traceProperty->setValue($exception, $trace);

        return $exception;
    }

    protected function createTemporaryExceptionFile($string)
    {
        $path = storage_path('statamic/tmp/yaml-'.md5($string));

        File::put($path, $string);

        app()->terminating(function () use ($path) {
            File::delete($path);
        });

        return $path;
    }
}
