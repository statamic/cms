<?php

namespace Statamic\View\Antlers;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Statamic\Statamic;
use Statamic\Support\Str;

class RegexError extends \Error implements ProvidesSolution
{
    protected $error;
    protected $view;

    public function __construct($error, $view)
    {
        parent::__construct();

        $this->error = $error;
        $this->view = $view;

        $this->setMessage();
    }

    protected function setMessage()
    {
        $this->message = array_flip(array_filter(get_defined_constants(true)['pcre'], function ($value) {
            return substr($value, -6) === '_ERROR';
        }, ARRAY_FILTER_USE_KEY))[$this->error];
    }

    public function getSolution(): Solution
    {
        $message = $this->message;
        $description = "Error {$this->message} enountered when parsing regular expression.";

        if ($this->message === 'PREG_BACKTRACK_LIMIT_ERROR') {
            $message = 'Regular expression backtrack limit reached';
            $description = 'This typically happens when a view is too large. You can try splitting it into partials.';
            if ($this->view) {
                $description .= "\\\n\\\nView: `".Str::after($this->view, base_path().'/').'`';
            }
        }

        return BaseSolution::create($message)
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read the Antlers guide' => Statamic::docsUrl('antlers'),
                'Read about the Partial tag' => Statamic::docsUrl('tags/partial'),
            ]);
    }
}
