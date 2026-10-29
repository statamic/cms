<script setup>
import { computed, ref, watch } from 'vue';
import { Icon } from '@ui';

// TEMP: the design options toggle in the bottom left, for flipping through options while one is picked.
// Click the label for the next option, and × rules out the one showing.
const props = defineProps({
    /** Where the choice and the ruled-out options are remembered */
    storageKey: { type: String, required: true },
    /** Each option's label, in the order of the write-up */
    options: { type: Array, required: true },
});

const model = defineModel({ type: Number, default: 0 });

const read = (key, fallback) => {
    try {
        return JSON.parse(localStorage.getItem(key)) ?? fallback;
    } catch {
        return fallback;
    }
};
const write = (key, value) => {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch {}
};

const skipped = ref(read(`${props.storageKey}-skipped`, []));
const remaining = computed(() => props.options.map((o, i) => i).filter((i) => !skipped.value.includes(i)));

model.value = read(props.storageKey, model.value);
if (!remaining.value.includes(model.value)) model.value = remaining.value[0];

const labelFor = (i) => `${remaining.value.indexOf(i) + 1}/${remaining.value.length} · ${props.options[i]}${skipped.value.length ? ` (#${i + 1})` : ''}`;

function next() {
    model.value = remaining.value[(remaining.value.indexOf(model.value) + 1) % remaining.value.length];
}

function skip() {
    if (remaining.value.length < 2) return;
    const current = model.value;
    next();
    skipped.value = [...skipped.value, current];
}

watch(model, (value) => write(props.storageKey, value));
watch(skipped, (value) => write(`${props.storageKey}-skipped`, value));
</script>

<template>
    <div class="fixed bottom-4 left-4 z-(--z-index-modal) flex items-center gap-1 font-mono text-xs" @pointerdown.stop>
        <button
            type="button"
            class="grid h-6.5 cursor-pointer items-center rounded-md border border-gray-300 bg-white px-2 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
            @mousedown.prevent
            @click="next"
        >
            <span v-for="i in remaining" :key="i" :class="['col-start-1 row-start-1 text-start', i === model ? '' : 'invisible']">{{ labelFor(i) }}</span>
        </button>
        <button
            v-if="remaining.length > 1"
            type="button"
            title="Rule this one out"
            class="flex size-6.5 shrink-0 cursor-pointer items-center justify-center rounded-md border border-gray-300 bg-white text-gray-500 shadow-sm hover:text-red-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
            @mousedown.prevent
            @click="skip"
        >
            <Icon name="x" class="size-3" />
        </button>
    </div>
</template>
