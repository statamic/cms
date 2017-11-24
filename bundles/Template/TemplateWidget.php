<?php namespace Statamic\Addons\Template;

use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Parse;
use Statamic\API\Config;
use Statamic\Extend\Widget;

class TemplateWidget extends Widget
{
    public function html()
    {
        // This approach would be nicer at some point.
        // return app('Statamic\Http\View')->render($this->config, $this->get('template'));        
        
        $template = $this->loadTemplate($this->get('template'));

        return Parse::template($template, $this->parameters);
    }

    /**
     * Gets the raw contents of the template
     *
     * @return string
     */
    private function loadTemplate($template)
    {
        $template_path = "templates/{$template}.html";

        if (File::disk('theme')->exists($template_path)) {
            return File::disk('theme')->get($template_path);
        }
    }
}
