import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const dirty = ref([]);
let inertiaWarningListener = null;
let dirtyUrl = null;
let dirtyState = null;

function names() {
    return dirty.value;
}

function clear() {
    dirty.value = [];
}

function count() {
    return dirty.value.length;
}

function add(name) {
    if (dirty.value.indexOf(name) == -1) {
        if (! dirty.value.length) {
            dirtyUrl = window.location.href;
            dirtyState = window.history.state;
        }
        dirty.value = [...dirty.value, name];
    }
}

function remove(name) {
    dirty.value = dirty.value.filter((n) => n !== name);
}

function isWarningEnabled() {
    return Statamic.$preferences.get('confirm_dirty_navigation', true);
}

function enableWarning() {
    if (! isWarningEnabled()) return;

    // For Inertia navigation (e.g. through Link component)
    inertiaWarningListener ??= router.on('before', event => {
        const confirmed = confirm(__('statamic::messages.dirty_navigation_warning'));
        if (confirmed) {
            // Clear state so subsequent navigations don't prompt again
            router.on('success', () => clear());
            // Disable the browser warning so the user doesn't get double prompts
            disableWarning();
        }
        return confirmed;
    });

    // For real page unload (refresh, tab close, cross-origin nav).
    // popstate (back/forward, trackpad swipe) is handled separately below.
    window.onbeforeunload = () => '';
}

function disableWarning() {
    window.onbeforeunload = null;
    inertiaWarningListener && inertiaWarningListener();
    inertiaWarningListener = null;
}

// Intercept browser back/forward (popstate) navigation. Inertia's popstate
// handler swaps pages without firing its `before` event, so we register at
// module load — before `createInertiaApp()` calls `eventHandler.init()` —
// to ensure our listener runs first and can block Inertia via
// `stopImmediatePropagation()`. See statamic/cms#14055.
window.addEventListener('popstate', (event) => {
    if (! dirty.value.length) return;
    if (! isWarningEnabled()) return;

    // Block Inertia's listener so it doesn't `setQuietly(..., { preserveState: false })`
    // and wipe the in-memory form data before we've confirmed.
    event.stopImmediatePropagation();

    // Re-push the dirty page we were just on so the URL/Inertia state are
    // restored while the (synchronous) confirm() is open and after a cancel.
    if (dirtyUrl && dirtyState) {
        window.history.pushState(dirtyState, '', dirtyUrl);
    }

    const confirmed = confirm(__('statamic::messages.dirty_navigation_warning'));

    if (! confirmed) return;

    clear();
    disableWarning();

    // We're now on a re-pushed entry of the dirty page. Going back fires
    // popstate again with the user's intended target; dirty is clean so
    // Inertia handles it normally.
    window.history.back();
});

function state(name, state) {
    state ? add(name) : remove(name);
}

function has(name) {
    return dirty.value.includes(name);
}

watch(
    dirty,
    (newNames) => {
        newNames.length ? enableWarning() : disableWarning();
    },
    { immediate: true },
);

export default function useDirtyState() {
    return {
        state,
        add,
        remove,
        names,
        count,
        has,
        disableWarning,
    };
}
