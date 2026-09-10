/**
 * Direct wiring from the global header burger to Nav.toggle().
 * Avoids Statamic.$events (tiny-emitter) and document-level click ordering races.
 */
let handler = null;

export function setCpNavToggleHandler(fn) {
    handler = fn;
}

export function clearCpNavToggleHandler(fn) {
    if (handler === fn) {
        handler = null;
    }
}

export function invokeCpNavToggle() {
    handler?.();
}
