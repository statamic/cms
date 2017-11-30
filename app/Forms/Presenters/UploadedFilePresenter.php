<?php

namespace Statamic\Forms\Presenters;

use Illuminate\Support\Collection;
use Statamic\API\Asset;
use Statamic\API\Helper;
use Statamic\Contracts\Forms\Submission;

class UploadedFilePresenter
{
    /**
     * @var Submission
     */
    private $submission;

    /**
     * @var string
     */
    private $field;

    /**
     * @var Collection
     */
    protected $config;

    /**
     * @var string|mixed
     */
    protected $value;

    /**
     * @var string
     */
    protected $type;

    private function __construct($submission, $field)
    {
        $this->submission = $submission;
        $this->field = $field;
        $this->config = collect(array_get($submission->fields(), $field));
        $this->value = $submission->get($field);
        $this->type = ucfirst(rtrim($this->config->get('type'), 's'));
    }

    public static function render($submission, $field)
    {
        return (new self($submission, $field))->buildHtml();
    }

    private function buildHtml()
    {
        if (! $value = $this->submission->get($this->field)) {
            return;
        }

        return collect(Helper::ensureArray($this->value))->map(function ($value) {
            return call_user_func([$this, "buildHtmlFor{$this->type}"], $value);
        })->implode('<br>');
    }

    private function buildHtmlForFile($file)
    {
        $url = '/' . $this->config->get('destination') . '/' . $file;

        return "<a href='$url'>$file</a>";
    }

    private function buildHtmlForAsset($url)
    {
        if ($asset = Asset::find($url)) {
            $url = $asset->url();
        }

        $file = urldecode(pathinfo($url)['basename']);

        // If the file doesn't exist, simply show the filename without a link.
        return ($asset) ? "<a href='$url' target='_blank'>$file</a>" : $file;
    }
}
