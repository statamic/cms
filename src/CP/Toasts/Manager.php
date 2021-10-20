<?php

namespace Statamic\CP\Toasts;

use Illuminate\Session\Store;

/**
 * Stores toasts in session until they are sent to the client.
 * Toasts are either sent along with the next JSON response or in the next view.
 *
 * @see \Statamic\Http\Middleware\CP\AddToasts
 * @see \Statamic\Http\View\Composers\JavascriptComposer
 */
class Manager
{
    private const SESSION_KEY = '_toasts';

    /**
     * @var Store
     */
    private $session;

    public function __construct(Store $session)
    {
        $this->session = $session;
    }

    public function push(Toast $toast)
    {
        $toasts = $this->getFromSession();

        array_push($toasts, $toast);

        $this->storeToSession($toasts);
    }

    /**
     * @return Toast[]
     */
    public function all(): array
    {
        return $this->getFromSession();
    }

    public function clear()
    {
        $this->storeToSession([]);
    }

    /**
     * @return Toast[]
     */
    private function getFromSession(): array
    {
        return $this->session->get(self::SESSION_KEY, []);
    }

    /**
     * @param  Toast[]  $toasts
     */
    private function storeToSession(array $toasts)
    {
        $this->session->flash(self::SESSION_KEY, $toasts);
    }
}
