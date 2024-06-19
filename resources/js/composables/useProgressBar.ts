import { computed, ref, watch } from 'vue';
import progress from 'nprogress';

const progressing = ref(false)
const names = ref([])
const timer = ref(null)

const count = computed(() => names.value.length)
const isComplete = computed(() => count.value === 0)

function start() {
    progressing.value = true;
    timer.value = setTimeout(() => progress.start(), 500);
}

function stop() {
    if (timer.value) {
        clearTimeout(timer.value);
    }

    progress.done();

    progressing.value = false;
}

function add(name: string) {
    if (names.value.indexOf(name) == -1) {
        names.value = [...names.value, name];
    }
}

function remove(name: string) {
    const newValues = [...names.value]

    const i = newValues.indexOf(name);

    if (i === -1) {
        return
    }

    newValues.splice(i, 1)

    names.value = newValues
}

function loading(name: string, loading: boolean) {
    if (loading) {
        add(name)
    } else {
        remove(name)
    }
}

watch(names, (newNames) => {
    if (newNames.length > 0 && !progressing.value) {
        start();
    }

    if (newNames.length === 0 && progressing.value) {
        stop();
    }
}, { immediate: true })

export default function useProgressBar() {
    // We defined the data outside the composable.
    // This way the data is shared along all components that use it.
    // @see https://vuejs.org/guide/scaling-up/state-management.html#simple-state-management-with-reactivity-api
    return {
        loading,
        start: add,
        complete: remove,
        names,
        count,
        isComplete,
    }
}