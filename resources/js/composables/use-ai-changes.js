import { reactive, watch } from 'vue';
import { nanoid } from 'nanoid';

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
    listenForBuilder();

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
        (names) => {
            [...changes.keys()].forEach((k) => names.includes(k.split(':')[0]) || changes.delete(k));
            if (!names.includes('blueprints')) blueprintChanges.clear();
        },
    );
}

// The ids of the fields the AI added to the blueprint in the editor, until it's saved (or you edit one), so they can be highlighted (and undone)
export const blueprintChanges = reactive(new Set());
// Each gets a Changed by AI badge, with Undo, and opens up with a glow in the AI colour (see RegularField.vue)

// The blueprint editor that's open, the same way: it's where the AI adds fields, as you would, leaving saving to you
let builder = null;
const waitingForBuilder = [];

function listenForBuilder() {
    Statamic.$events.$on('blueprint-builder-created', (instance) => {
        builder = instance;
        waitingForBuilder.splice(0).forEach((resolve) => resolve(instance));
    });
    Statamic.$events.$on('blueprint-builder-destroyed', (instance) => {
        if (builder === instance) builder = null;
        blueprintChanges.clear();
    });
}

// The open blueprint editor, or the next one to open (after navigating to a blueprint). Gives up after a few seconds.
function nextBuilder(timeout = 5000) {
    return new Promise((resolve) => {
        waitingForBuilder.push(resolve);
        setTimeout(() => resolve(null), timeout);
    });
}

// Adds a plain field of a type to the blueprint in the editor, in its first section, after the first field, and brings it into view
function addBlueprintField({ handle, type, display, icon = `fieldtype-${type}` }) {
    const section = builder?.$refs.tabs?.tabs?.[0]?.sections?.[0];
    if (!section) return null;

    const field = { _id: nanoid(), handle, type: 'inline', fieldtype: type, icon, config: { type, display, width: 100 } };
    section.fields.splice(Math.min(1, section.fields.length), 0, field);
    blueprintChanges.add(field._id);
    setTimeout(() => document.getElementById(field._id)?.scrollIntoView({ block: 'center', behavior: 'smooth' }), 100);

    return field;
}

// Takes a field the AI added back out of the blueprint
function undoBlueprintField(id) {
    for (const tab of builder?.$refs.tabs?.tabs ?? []) {
        for (const section of tab.sections) {
            const i = section.fields.findIndex((field) => field._id === id);
            if (i >= 0) section.fields.splice(i, 1);
        }
    }
    blueprintChanges.delete(id);
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
        record(form.name, path, form.values.value[path], value);
        form.setFieldValue(path, value);
        await new Promise((resolve) => setTimeout(resolve, gap));
    }
}

// Remember a change, keeping what the field held before the AI first changed it
function record(container, path, before, after) {
    const k = key(container, path);
    if (!changes.has(k)) changes.set(k, { before: before ?? null });
    Object.assign(changes.get(k), { after, at: Date.now() });
}

function forgetAll(container) {
    [...changes.keys()].filter((k) => k.startsWith(`${container}:`)).forEach((k) => changes.delete(k));
}

export default function useAiChanges() {
    listen();

    return {
        currentForm: () => form,
        nextForm,
        nextBuilder,
        addBlueprintField,
        undoBlueprintField,
        isBlueprintChange: (id) => blueprintChanges.has(id),
        // You edited it yourself, so it's yours now
        forgetBlueprintField: (id) => blueprintChanges.delete(id),
        fill,
        // A suggestion picked from a field's suggest button, badged the same as the chat's changes
        record,
        changeFor: (container, path) => changes.get(key(container, path)),
        // You edited it yourself, so it's yours now. Not straight after the AI filled it in, though: some fields (like Bard) send their
        // value back tidied up as they take it in, which isn't you editing it.
        forget: (container, path) => {
            const change = changes.get(key(container, path));
            if (change && Date.now() - change.at > 1000) changes.delete(key(container, path));
        },
        undo: (container, path) => {
            const change = changes.get(key(container, path));
            if (!change || form?.name !== container) return;
            form.setFieldValue(path, change.before);
            changes.delete(key(container, path));
        },
    };
}
