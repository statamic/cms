<?php

namespace Statamic\Updater\Presenters;

use Statamic\Support\Html;
use Statamic\Support\Str;

/** @deprecated */
class GithubReleasePresenter
{
    /**
     * @var string
     */
    private $githubRelease;

    /**
     * Instantiate github release presenter.
     *
     * @param  string  $githubRelease
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
        $string = Str::replace($string, '[new]', '<span class="label" style="background: #5bc0de;">NEW</span>');
        $string = Str::replace($string, '[fix]', '<span class="label" style="background: #5cb85c;">FIX</span>');
        $string = Str::replace($string, '[break]', '<span class="label" style="background: #d9534f;">BREAK</span>');
        $string = Str::replace($string, '[na]', '<span class="label" style="background: #e8e8e8;">N/A</span>');

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
