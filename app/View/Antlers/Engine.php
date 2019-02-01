<?php

namespace Statamic\View\Antlers;

use Statamic\API\Str;
use Statamic\API\Path;
use Statamic\API\Parse;
use Statamic\Exceptions;
use Illuminate\Filesystem\Filesystem;
use Statamic\Extend\Management\TagLoader;
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
     * Full path to the view
     *
     * @var string
     */
    private $path;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Create a new AntlersEngine instance
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
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
        $contents = $this->getContents($path);

        list($frontMatter, $contents) = $this->extractFrontMatter($contents);

        return app('antlers.view.parser')->parse(
            $contents,
            array_merge($data, $frontMatter),
            ['Statamic\View\Antlers\Engine', 'renderTag'],
            Str::endsWith($path, '.php')
        );
    }

    protected function getContents($path)
    {
        return $this->filesystem->get($path);
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
