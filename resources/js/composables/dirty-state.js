import { ref, watch } from 'vue';

const dirty = ref([]);

function names() {
    return dirty.value;
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
    dirty.value = dirty.value.filter(n => n !== name);
}

function enableWarning() {
    if (Statamic.$preferences.get('confirm_dirty_navigation', true)) {
        window.onbeforeunload = () => '';
    }
}

function disableWarning() {
    window.onbeforeunload = null;
}

function state(name, state) {
    state ? add(name) : remove(name);
}

function has(name) {
    return dirty.value.includes(name)
}

watch(dirty, (newNames) => {
    newNames.length ? enableWarning() : disableWarning();
}, { immediate: true })

export default function useDirtyState() {
    return {
        state,
        add,
        remove,
        names,
        count,
        has,
        disableWarning,
    }
}
