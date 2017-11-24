<?php

namespace Statamic\API;

use Spyc;

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
    public static function parse($str)
    {
        if (empty($str)) {
            return [];
        }

        if (Pattern::startsWith($str, '---')) {
            $split = preg_split("/\n---/", $str, 2, PREG_SPLIT_NO_EMPTY);
            $yaml = $split[0];
            $content = ltrim(array_get($split, 1, ''));

            return Spyc::YAMLLoadString($yaml) + ['content' => $content];
        }

        return Spyc::YAMLLoadString($str);
    }

    /**
     * Dump some YAML
     *
     * @param array        $data
     * @param string|bool  $content
     * @return string
     */
    public static function dump($data, $content = false)
    {
        $yaml = Spyc::YAMLDump($data, 2, 100);
        $yaml = substr($yaml, 4); // remove the initial fencing by spyc

        if ($content) {
            $fenced = "---".PHP_EOL . $yaml . "---".PHP_EOL;
            $yaml = $fenced . $content;
        }

        return $yaml ?: '';
    }
}
