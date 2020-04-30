<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class PhpInfoController extends CpController
{
    public function __invoke(Request $request)
    {
        return view('statamic::utilities.phpinfo', [
            'phpinfo' => $this->phpinfo(),
        ]);
    }

    /**
     * Parses and returns PHP info.
     *
     * Hat tip to Craft for this magic.
     * https://github.com/craftcms/cms/blob/develop/src/utilities/PhpInfo.php#L63
     */
    protected function phpinfo()
    {
        ob_start();
        phpinfo(-1);
        $phpInfoStr = ob_get_clean();

        $replacePairs = [
            '#^.*<body>(.*)</body>.*$#ms' => '$1',
            '#<h2>PHP License</h2>.*$#ms' => '',
            '#<h1>Configuration</h1>#' => '',
            "#\r?\n#" => '',
            '#</(h1|h2|h3|tr)>#' => '</$1>'."\n",
            '# +<#' => '<',
            "#[ \t]+#" => ' ',
            '#&nbsp;#' => ' ',
            '#  +#' => ' ',
            '# class=".*?"#' => '',
            '%&#039;%' => ' ',
            '#<tr>(?:.*?)"src="(?:.*?)=(.*?)" alt="PHP Logo" /></a><h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#' => '<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'."\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
            '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#' => '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
            '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#' => '<tr><td>Zend Engine</td><td>$2</td></tr>'."\n".'<tr><td>Zend Egg</td><td>$1</td></tr>',
            '# +#' => ' ',
            '#<tr>#' => '%S%',
            '#</tr>#' => '%E%',
        ];

        $phpInfoStr = preg_replace(array_keys($replacePairs), array_values($replacePairs), $phpInfoStr);

        $sections = explode('<h2>', strip_tags($phpInfoStr, '<h2><th><td>'));
        unset($sections[0]);

        $phpInfo = [];

        foreach ($sections as $section) {
            $heading = substr($section, 0, strpos($section, '</h2>'));

            if (preg_match_all('#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#', $section, $matches, PREG_SET_ORDER) !== 0) {
                /** @var array[] $matches */
                foreach ($matches as $row) {
                    if (! isset($row[2])) {
                        continue;
                    }

                    if (! isset($row[3]) || $row[2] === $row[3]) {
                        $value = $row[2];
                    } else {
                        $value = array_slice($row, 2);
                    }

                    $name = $row[1];
                    $phpInfo[$heading][$name] = $this->redactIfSensitive($name, $value);
                }
            }
        }

        return $phpInfo;
    }

    protected function redactIfSensitive($name, $value)
    {
        $sensitiveKeywords = [
            'key',
            'pass',
            'password',
            'pw',
            'secret',
            'tok',
            'token',
        ];

        if (is_array($value)) {
            foreach ($value as $n => &$v) {
                $v = $this->redactIfSensitive($n, $v);
            }
        } elseif (
            is_string($value) &&
            preg_match('/\b('.implode('|', $sensitiveKeywords).')\b/', $this->camelToWords($name, false))
        ) {
            $value = str_repeat('•', strlen($value));
        }

        return $value;
    }

    protected function camelToWords($name)
    {
        $label = strtolower(trim(str_replace([
            '-',
            '_',
            '.',
        ], ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name))));

        return $label;
    }
}
