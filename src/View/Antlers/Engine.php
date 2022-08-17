<?php

namespace Statamic\View\Antlers;

use Facades\Statamic\View\Cascade;
use Illuminate\Contracts\View\Engine as EngineInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\Exceptions;
use Statamic\Facades\Compare;
use Statamic\Facades\Parse;
use Statamic\Fields\Value;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Tags\Loader as TagLoader;
use Statamic\Tags\TagNotFoundException;

class Engine implements EngineInterface
{
    const EXTENSIONS = [
        'antlers.html',
        'antlers.php',
        'antlers.xml',
    ];

    /**
     * The Antlers Parser.
     *
     * @return Parser
     */
    private $parser;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Array of whether noparse extractions should be injected. The last
     * value is always the most recent view / innermost view, since
     * views can be parsed inside other views (partials).
     *
     * @var array
     */
    private $injectExtractions = [];

    /**
     * Create a new AntlersEngine instance.
     *
     * @param  Filesystem  $filesystem
     * @param  Parser  $parser
     */
    public function __construct(Filesystem $filesystem, Parser $parser)
    {
        $this->filesystem = $filesystem;
        $this->parser = $parser;
    }

    /**
     * Prevent injecting extractions the next time a view is evaluated.
     *
     * @return self
     */
    public function withoutExtractions()
    {
        $this->injectExtractions[] = false;

        return $this;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array  $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        $parser = $this->parser->allowPhp(Str::endsWith($path, '.php'));

        $contents = $this->getContents($path);

        [$frontMatter, $contents] = $this->extractFrontMatter($contents);

        // If the data has provided front matter with this special key, it will override
        // front matter defined in the view itself. This is typically used by partials.
        // ie. data defined in the partial tag parameters will win the array merge.
        $frontMatter = array_merge($frontMatter, Arr::pull($data, '__frontmatter', []));

        $views = Cascade::get('views', []);
        $views[$path] = $frontMatter;
        Cascade::set('views', $views);

        $contents = $parser->parseView($path, $contents, $data);

        if (array_pop($this->injectExtractions) === false) {
            $contents->withoutExtractions();
        }

        return (string) $contents;
    }

    protected function getContents($path)
    {
        return $this->filesystem->get($path);
    }

    /**
     * Get the YAML front matter and contents from a view.
     *
     * @param  string  $contents
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
     * @param  string  $name  Plugin tag name
     * @param  array  $parameters  Tag parameters
     * @param  string  $content  If its a tag pair, this is what's between them
     * @param  array  $context  The tag's surrounding context variables
     * @return mixed|string
     *
     * @throws Exceptions\FatalException
     * @throws \Exception
     */
    public static function renderTag(Parser $parser, $name, $parameters = [], $content = '', $context = [])
    {
        $tag_measure = 'tag_'.$name.microtime();
        debugbar()->startMeasure($tag_measure, 'Tag: '.$name);

        // determine format
        if ($pos = strpos($name, ':')) {
            $original_method = substr($name, $pos + 1);
            $method = Str::camel($original_method);
            $name = substr($name, 0, $pos);
        } else {
            $method = $original_method = 'index';
        }

        try {
            $tag = app(TagLoader::class)->load($name, [
                'parser'     => $parser,
                'params'     => $parameters,
                'content'    => $content,
                'context'    => $context,
                'tag'        => $name.':'.$original_method,
                'tag_method' => $original_method,
            ]);

            $output = call_user_func([$tag, $method]);

            if (Compare::isQueryBuilder($output)) {
                $output = $output->get();
            }

            if ($output instanceof Collection) {
                $output = $output->toAugmentedArray();
            }

            if ($output instanceof Augmentable) {
                $output = $output->toAugmentedArray();
            }

            // Allow tags to return an array. We'll parse it for them.
            if (is_array($output)) {
                if (empty($output)) {
                    $output = $tag->parseNoResults();
                } else {
                    $output = Arr::assoc($output) ? $tag->parse($output) : $tag->parseLoop($output);
                }
            }

            if ($output instanceof Value) {
                $output = $output->antlersValue($parser, $context);
            }

            return $output;
        } catch (TagNotFoundException $e) {
            return;
        } catch (Exceptions\FatalException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        } finally {
            debugbar()->stopMeasure($tag_measure);
        }
    }
}
