import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const dirty = ref([]);
let inertiaWarningListener = null;

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
        dirty.value = [...dirty.value, name];
    }
}

function remove(name) {
    dirty.value = dirty.value.filter((n) => n !== name);
}

function enableWarning() {
    if (! Statamic.$preferences.get('confirm_dirty_navigation', true)) {
        return;
    }

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

    // For browser navigation (e.g. back button, refresh, closing tab)
    window.onbeforeunload = () => '';
}

function disableWarning() {
    window.onbeforeunload = null;
    inertiaWarningListener && inertiaWarningListener();
}

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
