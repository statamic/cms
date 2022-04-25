<?php

namespace Statamic\CP\Toasts;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

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
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function push(Toast $toast)
    {
        $toasts = $this->getFromSession();

        array_push($toasts, $toast);

        $this->storeToSession($toasts);
    }

    public function info(string $message): Toast
    {
        return tap(new Toast($message, 'info'), function ($toast) {
            $this->push($toast);
        });
    }

    public function error(string $message): Toast
    {
        return tap(new Toast($message, 'error'), function ($toast) {
            $this->push($toast);
        });
    }

    public function success(string $message): Toast
    {
        return tap(new Toast($message, 'success'), function ($toast) {
            $this->push($toast);
        });
    }

    /**
     * @return Toast[]
     */
    public function all(): array
    {
        return $this->getFromSession();
    }

    public function collect(): Collection
    {
        return collect($this->all());
    }

    public function toArray(): array
    {
        return $this->collect()->toArray();
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
