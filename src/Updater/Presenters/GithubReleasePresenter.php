<?php

namespace Statamic\Updater\Presenters;

use Statamic\Support\Html;
use Statamic\Support\Str;

class GithubReleasePresenter
{
    /**
     * @var string
     */
    private $githubRelease;

    /**
     * Instantiate github release presenter.
     *
     * @param string $githubRelease
     */
    public function __construct(string $githubRelease)
    {
        $this->githubRelease = $githubRelease;
    }

    /**
     * Convert github release to HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        $string = Html::markdown($this->githubRelease ?: '- [na] Changelog unavailable.');

        // TODO: Move to blade or vue? Or leave in presenter?
        // TODO: Create tailwind classes for these labels.
        $replacements = [
            '[new]' => '<span class="label" style="background: #5bc0de;">NEW</span>',
            '[fix]' => '<span class="label" style="background: #5cb85c;">FIX</span>',
            '[break]' => '<span class="label" style="background: #d9534f;">BREAK</span>',
            '[na]' => '<span class="label" style="background: #e8e8e8;">N/A</span>',
        ];

        foreach ($replacements as $search => $replace) {
            $string = method_exists(Str::class, 'replace')
                ? Str::replace($search, $replace, $string) // Laravel >= 8.41.0
                : Str::replace($string, $search, $replace);
        }
        
        return $string;
    }

    /**
     * Output to HTML when cast as string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toHtml();
    }
}
