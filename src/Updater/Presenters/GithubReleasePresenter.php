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
        $string = Str::replace($string, '[new]', '<span class="label block text-center text-white rounded" style="background: #5bc0de; padding: 2px; padding-bottom: 1px;">NEW</span>');
        $string = Str::replace($string, '[fix]', '<span class="label block text-center text-white rounded" style="background: #5cb85c; padding: 2px; padding-bottom: 1px;">FIX</span>');
        $string = Str::replace($string, '[break]', '<span class="label block text-center text-white rounded" style="background: #d9534f; padding: 2px; padding-bottom: 1px;">BREAK</span>');
        $string = Str::replace($string, '[na]', '<span class="label block text-center text-white rounded" style="background: #e8e8e8; padding: 2px; padding-bottom: 1px;">N/A</span>');

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
