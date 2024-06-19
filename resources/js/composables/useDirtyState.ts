import { computed, ref, watch } from 'vue';
import progress from 'nprogress';

const names = ref([])

const count = computed(() => names.value.length)

function add(name: string) {
    if (names.value.indexOf(name) == -1) {
        names.value = [...names.value, name];
    }
}

function remove(name: string) {
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

function state(name: string, state: boolean) {
    if (state) {
        add(name)
    } else {
        remove(name)
    }
}

function has(name: string) {
    return names.value.includes(name)
}


watch(names, (newNames) => {
    if (newNames.length) {
        enableWarning();
    }

    if (newNames.length === 0) {
        disableWarning();
    }
}, { immediate: true })

export default function useDirtyState() {
    // We defined the data outside the composable.
    // This way the data is shared along all components that use it.
    // @see https://vuejs.org/guide/scaling-up/state-management.html#simple-state-management-with-reactivity-api
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