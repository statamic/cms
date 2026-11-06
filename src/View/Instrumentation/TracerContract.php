<?php

namespace Statamic\View\Instrumentation;

/** @internal */
interface TracerContract
{
    public function onEnter(Span $span): mixed;

    public function onExit(Span $span, mixed $handle, mixed $output): void;

    public function onRenderComplete(): void;
}
