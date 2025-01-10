import { ref, watch } from 'vue';

const names = ref([]);

function count() {
    return names.value.length;
}

function add(name) {
    if (names.value.indexOf(name) == -1) {
        names.value = [...names.value, name];
    }
}

function remove(name) {
    names.value = names.value.filter(n => n !== name);
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
    return names.value.includes(name)
}

watch(names, (newNames) => {
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
