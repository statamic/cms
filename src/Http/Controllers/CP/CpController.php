<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Support\Str;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Facades\Folder;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;
use Statamic\Exceptions\AuthorizationException;
use Illuminate\Auth\Access\AuthorizationException as LaravelAuthException;

/**
 * The base control panel controller
 */
class CpController extends Controller
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new CpController
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get all the template names from the current theme
     *
     * @return array
     */
    public function templates()
    {
        $templates = [];

        foreach (Folder::disk('resources')->getFilesByTypeRecursively('templates', 'html') as $path) {
            $parts = explode('/', $path);
            array_shift($parts);
            $templates[] = Str::removeRight(join('/', $parts), '.html');
        }

        return $templates;
    }

    public function themes()
    {
        $themes = [];

        foreach (Folder::disk('themes')->getFolders('/') as $folder) {
            $name = $folder;

            // Get the name if one exists in a meta file
            if (File::disk('themes')->exists($folder.'/meta.yaml')) {
                $meta = YAML::parse(File::disk('themes')->get($folder.'/meta.yaml'));
                $name = array_get($meta, 'name', $folder);
            }

            $themes[] = compact('folder', 'name');
        }

        return $themes;
    }

    /**
     * 404
     */
    public function pageNotFound()
    {
        return response()->view('statamic::errors.404', [], 404);
    }

    public function authorize($ability, $args = [], $message = null)
    {
        $message = $message ?? __('This action is unauthorized.');

        try {
            return parent::authorize($ability, $args);
        } catch (LaravelAuthException $e) {
            throw new AuthorizationException($message);
        }
    }

    public function authorizeIf($condition, ...$args)
    {
        if ($condition) {
            return $this->authorize(...$args);
        }
    }
}
