<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Auth\Access\AuthorizationException as LaravelAuthException;
use Illuminate\Http\Request;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Http\Controllers\Controller;
use Statamic\Statamic;

/**
 * The base control panel controller.
 */
class CpController extends Controller
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Create a new CpController.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 404.
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

    public function authorizeIf($condition, $ability, $args = [], $message = null)
    {
        if ($condition) {
            return $this->authorize($ability, $args, $message);
        }
    }

    public function authorizePro()
    {
        if (! Statamic::pro()) {
            throw new AuthorizationException(__('Statamic Pro is required.'));
        }
    }

    public function authorizeProIf($condition)
    {
        if ($condition) {
            return $this->authorizePro();
        }
    }

    public function requireElevatedSession(): void
    {
        abort_if(
            boolean: ! request()->hasElevatedSession(),
            code: 403,
            message: __('Requires an elevated session.')
        );
    }
}
