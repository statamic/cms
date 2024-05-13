<?php

namespace Statamic\Yaml;

use Exception;
use ReflectionProperty;
use Statamic\Facades\File;
use Statamic\Facades\Pattern;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Yaml\ParseException as StatamicParseException;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class Yaml
{
    protected $file;
    protected $yaml;

    public function __construct(SymfonyYaml $yaml)
    {
        $this->yaml = $yaml;
    }

    public function file($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Parse a string of raw YAML into an array.
     *
     * @param  null|string  $str  The YAML string
     * @return array
     */
    public function parse($str = null)
    {
        $originalStr = $str;

        if (func_num_args() === 0) {
            throw_if(! $this->file, new Exception('Cannot parse YAML without a file or string.'));
            $str = File::get($this->file);
        }

        if (empty($str)) {
            $this->file = null;

            return [];
        }

        $content = null;

        if (Pattern::startsWith($str, '---')) {
            $split = preg_split("/\n---/", $str, 2, PREG_SPLIT_NO_EMPTY);
            $str = $split[0];
            if (empty($content = ltrim(Arr::get($split, 1, '')))) {
                $content = null;
            }
        }

        try {
            $yaml = $this->yaml->parse($str);
        } catch (\Exception $e) {
            throw $this->viewException($e, $str);
        }

        $this->validateString($yaml, $str);
        $this->validateDocumentContent($yaml, $content, $originalStr);

        $this->file = null;

        return isset($content)
            ? $yaml + ['content' => $content]
            : $yaml;
    }

    /**
     * Dump some YAML.
     *
     * @param  array  $data
     * @param  string|bool  $content
     * @return string
     */
    public function dump($data, $content = null)
    {
        if (! is_null($content)) {
            if (is_string($content)) {
                return $this->dumpFrontMatter($data, $content);
            }

            $data['content'] = $content;
        }

        return $this->yaml->dump($data, 100, 2, SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }

    /**
     * Dump some YAML with fenced front-matter.
     *
     * @param  array  $data
     * @param  string|bool  $content
     * @return string
     */
    public function dumpFrontMatter($data, $content = null)
    {
        if (! is_null($content) && ! is_string($content)) {
            $data['content'] = $content;
            $content = '';
        }

        return '---'.PHP_EOL.rtrim($this->dump($data)).PHP_EOL.'---'.PHP_EOL.$content;
    }

    protected function viewException($e, $str, $line = null)
    {
        if ($this->file && File::exists($this->file)) {
            $path = $this->file;
        } else {
            $path = $this->createTemporaryExceptionFile($str, $this->file);
        }

        $args = [
            $e->getMessage(), 0, 1, $path,
            $line ?? $e->getParsedLine(),
            $e,
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

    protected function createTemporaryExceptionFile($string, $path = null)
    {
        $path = storage_path('statamic/tmp/yaml/'.($path ?? md5($string)));

        File::put($path, $string);

        app()->terminating(function () use ($path) {
            File::delete($path);
        });

        return $path;
    }

    protected function validateDocumentContent($yaml, $content, $str)
    {
        if (! $content || ! isset($yaml['content'])) {
            return;
        }

        $e = new StatamicParseException('You cannot have a YAML variable named "content" while document content is present');

        foreach (collect(explode("\n", $str))->reverse() as $i => $text) {
            if ($text === '---') {
                $line = $i + 2;
                break;
            }
        }

        throw $this->viewException($e, $str, $line);
    }

    protected function validateString($parsed, $string)
    {
        if (! is_string($parsed)) {
            return;
        }

        $snippet = Str::before($string, "\n");

        $exception = new \Exception("Unable to parse (near \"$snippet\").");

        throw $this->viewException($exception, $string, 1);
    }
}
