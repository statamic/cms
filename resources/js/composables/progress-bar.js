import { ref } from 'vue';
import progress from 'nprogress';

progress.configure({ showSpinner: false });

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

        if (!progressing.value) start();
    }
}

function remove(name) {
    const newValues = [...progressNames.value];
    const i = newValues.indexOf(name);

    if (i === -1) return;

    newValues.splice(i, 1);
    progressNames.value = newValues;

    if (newValues.length === 0 && progressing.value) stop();
}

function loading(name, loading) {
    loading ? add(name) : remove(name);
}

function count() {
    return progressNames.value.length;
}

function isComplete() {
    // Derived from the coarse `progressing` boolean rather than the length of
    // `progressNames` so that reactive consumers (e.g. a form's "is loading"
    // state) are only notified when loading starts and stops, not on every
    // individual operation. Otherwise many fields loading at once (e.g. lots of
    // relationship fields) can trigger enough updates to exceed Vue's recursive
    // update limit. See https://github.com/statamic/cms/issues/14787
    return !progressing.value;
}

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
