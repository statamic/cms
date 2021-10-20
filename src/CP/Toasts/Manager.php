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
    private static $TOASTS_SESSION_KEY = '_toasts';

    /**
     * @var Store
     */
    private $sessionStore;

    public function __construct(
        Store $sessionStore
    ) {
        $this->sessionStore = $sessionStore;
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
        return $this->sessionStore->get(self::$TOASTS_SESSION_KEY, []);
    }

    /**
     * @param  Toast[]  $toasts
     */
    private function storeToSession(array $toasts)
    {
        $this->sessionStore->flash(self::$TOASTS_SESSION_KEY, $toasts);
    }
}
