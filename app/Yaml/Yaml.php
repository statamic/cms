<?php

namespace Statamic\Yaml;

use Statamic\API\Pattern;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Statamic\Exceptions\WhoopsHandler as Whoops;

class Yaml
{
    /**
     * Parse a string of raw YAML into an array
     *
     * @param string $str  The YAML string
     * @return array
     */
    public function parse($str)
    {
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
            Whoops::addDataTable('YAML', ['string' => $str]);
            throw $e;
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
}
