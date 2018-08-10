<?php

namespace Statamic\API\Endpoint;

use Statamic\API\Pattern;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * Parsing and dumping YAML
 */
class YAML
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
            $yaml = $split[0];
            $content = ltrim(array_get($split, 1, ''));

            return SymfonyYaml::parse($yaml) + ['content' => $content];
        }

        return SymfonyYaml::parse($str);
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
}
