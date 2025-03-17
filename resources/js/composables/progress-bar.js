import { ref, watch } from 'vue';
import progress from 'nprogress';

const progressing = ref(false);
const progressNames = ref([]);
const timer = ref(null);

function names() {
    return progressNames.value;
}

function start() {
    progressing.value = true;
    timer.value = setTimeout(() => progress.start(), 500);
}

function stop() {
    if (timer.value) clearTimeout(timer.value);
    progress.done();
    progressing.value = false;
}

function add(name) {
    if (progressNames.value.indexOf(name) == -1) {
        progressNames.value = [...progressNames.value, name];
    }
}

function remove(name) {
    const newValues = [...progressNames.value];
    const i = newValues.indexOf(name);

    if (i === -1) return;

    newValues.splice(i, 1);
    progressNames.value = newValues;
}

function loading(name, loading) {
    loading ? add(name) : remove(name);
}

function count() {
    return progressNames.value.length;
}

function isComplete() {
    return count() === 0;
}

watch(
    names,
    (newNames) => {
        if (newNames.length > 0 && !progressing.value) {
            start();
        }

        if (newNames.length === 0 && progressing.value) {
            stop();
        }
    },
    { immediate: true },
);

export default function useProgressBar() {
    return {
        loading,
        start: add,
        complete: remove,
        names,
        count,
        isComplete,
    };
}
