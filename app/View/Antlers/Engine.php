<?php

namespace Statamic\View\Antlers;

use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Parse;
use Statamic\DataStore;
use Statamic\API\Helper;
use Statamic\API\Config;
use Statamic\Exceptions;
use Statamic\View\Antlers\Parser;
use Statamic\Extend\Management\TagLoader;
use Statamic\Exceptions\FileNotFoundException;
use Illuminate\Contracts\View\Engine as EngineInterface;

class Engine implements EngineInterface
{
    /**
     * Data to be injected into the view
     *
     * @var array
     */
    private $data;

    /**
     * Full path to the view/template
     *
     * @var string
     */
    private $path;

    /**
     * @var DataStore
     */
    private $store;

    /**
     * Create a new AntlersEngine instance
     *
     * @param DataStore $store
     */
    public function __construct(DataStore $store)
    {
        $this->store = $store;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array   $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        $this->path = $path;
        $this->data = $data;

        // Get the view contents, along with any front-matter.
        list($layout_front_matter, $raw_layout) = $this->extractFrontMatter($this->loadLayout());
        list($template_front_matter, $raw_template) = $this->extractFrontMatter($this->loadTemplate());

        // Merge in any view level data
        $this->store->merge($layout_front_matter);
        $this->store->merge($template_front_matter);

        // Render the template
        $rendered_template = Parse::template($raw_template, $this->store->getAll());

        // The template will get injected into the layout's {{ template_content }} tag
        $this->store->merge(['template_content' => $rendered_template]);

        // Render the layout
        $rendered_layout = Parse::template($raw_layout, $this->store->getAll());

        // Anything that was avoided with {{ noparse }} tags, put them back in now that we're done
        $html = Parser::injectNoparse($rendered_layout);

        return $html;
    }

    /**
     * Get the YAML front matter and contents from a view
     *
     * @param string $contents
     * @return array
     */
    private function extractFrontMatter($contents)
    {
        $parsed = Parse::frontMatter($contents);

        return [$parsed['data'], $parsed['content']];
    }

    /**
     * Gets the raw contents of the layout
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function loadLayout()
    {
        foreach (Helper::ensureArray($this->data['layout']) as $layout) {
            $layout_path = "layouts/{$layout}.html";

            if (File::disk('theme')->exists($layout_path)) {
                return File::disk('theme')->get($layout_path);
            }
        }

        // If there's no layout file available, we're not going to be getting very far. Let's
        // throw an exception here to kill the request. It doesn't make sense to continue.
        throw new FileNotFoundException("Layout [{$this->data['layout']}] doesn't exist.");
    }

    /**
     * Gets the raw contents of the template
     *
     * @return string
     */
    private function loadTemplate()
    {
        return File::get($this->path);
    }

    /**
     * Render tags
     * If the Parser comes across any plugin tags, this method will be called.
     *
     * @param string $name        Plugin tag name
     * @param array  $parameters  Tag parameters
     * @param string $content     If its a tag pair, this is what's between them
     * @param array  $context     The tag's surrounding context variables
     * @return mixed|string
     * @throws Exceptions\FatalException
     * @throws \Exception
     */
    public static function renderTag($name, $parameters = [], $content = '', $context = [])
    {
        $tag_measure = 'tag_' . $name . microtime();
        start_measure($tag_measure, 'Tag: ' . $name);

        // determine format
        if ($pos = strpos($name, ':')) {
            $original_method  = substr($name, $pos + 1);
            $method = Str::camel($original_method);
            $name    = substr($name, 0, $pos);
        } else {
            $method = $original_method = 'index';
        }

        try {
            $tag = app(TagLoader::class)->load($name, [
                'parameters' => $parameters,
                'content'    => $content,
                'context'    => $context,
                'tag'        => $name . ':' . $original_method,
                'tag_method' => $original_method
            ]);

            $output = call_user_func([$tag, $method]);

            if (is_array($output)) {
                $output = Parse::template($content, $output, $context);
            }

            return $output;
        } catch (Exceptions\ResourceNotFoundException $e) {
            // do nothing, this is ok
        } catch (Exceptions\FatalException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }

        stop_measure($tag_measure);
    }
}
