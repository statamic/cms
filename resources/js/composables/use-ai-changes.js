import { reactive, watch } from 'vue';

// Prototype: the AI chat changes an entry by filling in its publish form, the same as you typing in it, and leaves saving to you.
// This remembers which fields it changed, and what they held before, so they can be badged (and undone) until the form is saved.

// `${container}:${path}` → { before }, what the field held before the AI changed it
const changes = reactive(new Map());
const key = (container, path) => `${container}:${path}`;

// The publish form that's open, from the event every publish container sends when it mounts
let form = null;
let listening = false;
const waiting = [];

function listen() {
    if (listening) return;
    listening = true;

    Statamic.$events.$on('publish-container-created', (container) => {
        form = container;
        waiting.splice(0).forEach((resolve) => resolve(container));
    });
    Statamic.$events.$on('publish-container-destroyed', ({ name }) => {
        if (form?.name === name) form = null;
        forgetAll(name);
    });
    // Once the form is saved (it's no longer dirty), the changes are yours, so stop marking them.
    watch(
        () => Statamic.$dirty.names(),
        (names) => [...changes.keys()].forEach((k) => names.includes(k.split(':')[0]) || changes.delete(k)),
    );
}

// The open form, or the next one to open (after navigating to an entry). Gives up after a few seconds, e.g. when the unsaved changes warning stopped the visit.
function nextForm(timeout = 5000) {
    return new Promise((resolve) => {
        waiting.push(resolve);
        setTimeout(() => resolve(null), timeout);
    });
}

// Fill in fields, one after another, like it's typing them. Remembers what each held first.
async function fill(values, gap = 350) {
    if (!form) return;

    for (const [path, value] of Object.entries(values)) {
        const k = key(form.name, path);
        if (!changes.has(k)) changes.set(k, { before: form.values.value[path] ?? null });
        form.setFieldValue(path, value);
        await new Promise((resolve) => setTimeout(resolve, gap));
    }
}

function forgetAll(container) {
    [...changes.keys()].filter((k) => k.startsWith(`${container}:`)).forEach((k) => changes.delete(k));
}

export default function useAiChanges() {
    listen();

    return {
        currentForm: () => form,
        nextForm,
        fill,
        changeFor: (container, path) => changes.get(key(container, path)),
        // You edited it yourself, so it's yours now
        forget: (container, path) => changes.delete(key(container, path)),
        undo: (container, path) => {
            const change = changes.get(key(container, path));
            if (!change || form?.name !== container) return;
            form.setFieldValue(path, change.before);
            changes.delete(key(container, path));
        },
    };
}
