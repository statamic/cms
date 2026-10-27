<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Button, Select, Textarea } from '@ui';
import diffWords from '@/components/ai-suggest/diff-words.js';
import { tryFieldPrompt, tryPagePrompt } from '@/components/ai-suggest/mock-prompt-tries.js';

// Prototype: picks something to try a saved prompt on, runs it (mocked) and shows what it would do. Nothing is changed or saved.
const props = defineProps({
    /** The prompt as it is in the form now, saved or not */
    prompt: { type: Object, required: true },
    /** Entries to try it on, each with its text fields */
    entries: { type: Array, default: () => [] },
});

const forFields = computed(() => props.prompt.use_on === 'fields');

// Only the entries the prompt is offered on, and only ones with some text to work with
const entries = computed(() =>
    props.entries.filter(
        (entry) => (!props.prompt.collections?.length || props.prompt.collections.includes(entry.collection)) && entry.fields.some((field) => field.value.trim()),
    ),
);

// A page prompt runs on a whole entry. A field prompt runs on one field of one, or on some text of your own.
const OWN = '__own';
const options = computed(() => {
    if (!forFields.value) {
        return entries.value.map((entry) => ({ value: entry.id, label: `${entry.title} · ${entry.collection_title}` }));
    }

    return [
        ...entries.value.flatMap((entry) =>
            entry.fields
                .filter((field) => field.value.trim())
                // The longest first, as it shows the most
                .toSorted((a, b) => b.value.length - a.value.length)
                .map((field) => ({ value: `${entry.id}::${field.handle}`, label: `${entry.title} · ${field.display}` })),
        ),
        { value: OWN, label: __('Your own text…') },
    ];
});

const target = ref(null);
const ownText = ref('');

// Start on the first one, and move on if the one picked stops being offered (the collections changed, say)
watch(
    options,
    (list) => {
        if (!list.some((option) => option.value === target.value)) target.value = list[0]?.value ?? null;
    },
    { immediate: true },
);

const instructions = computed(() => (props.prompt.instructions || '').trim());
const canTry = computed(() => instructions.value && target.value && (target.value !== OWN || ownText.value.trim()));

const running = ref(false);
const result = ref(null);
const triedWith = ref(null);
const current = computed(() => JSON.stringify([instructions.value, target.value, ownText.value, props.prompt.use_on]));
// Once anything it ran with changes, the result is out of date until it's tried again
const stale = computed(() => result.value && triedWith.value !== current.value);

function run() {
    if (!canTry.value || running.value) return;

    running.value = true;
    const ranWith = current.value;

    setTimeout(() => {
        if (!forFields.value) {
            result.value = tryPagePrompt(instructions.value, entries.value.find((entry) => entry.id === target.value));
        } else if (target.value === OWN) {
            result.value = tryFieldPrompt(instructions.value, ownText.value.trim());
        } else {
            const [id, handle] = target.value.split('::');
            const field = entries.value.find((entry) => entry.id === id).fields.find((field) => field.handle === handle);
            result.value = tryFieldPrompt(instructions.value, field.value, ['textarea', 'markdown'].includes(field.type));
        }

        triedWith.value = ranWith;
        running.value = false;
    }, 900);
}

// It's shown when Try it is pressed, so it tries it straight away
onMounted(run);
</script>


<template>
    <div class="space-y-3">
        <div class="flex flex-wrap items-center gap-2">
            <Select
                v-model="target"
                :options="options"
                size="sm"
                :placeholder="forFields ? __('Pick a field') : __('Pick an entry')"
                class="min-w-0 flex-1 sm:max-w-96"
                :aria-label="forFields ? __('Field to try it on') : __('Entry to try it on')"
            />
            <Button
                size="sm"
                icon="ai-chat"
                :text="result ? __('Try again') : __('Try it')"
                :class="running && 'motion-safe:[&_svg]:animate-pulse'"
                :aria-disabled="!canTry || running"
                :disabled="!canTry"
                @click="run"
            />
        </div>

        <Textarea v-if="target === OWN" v-model="ownText" rows="2" :placeholder="__('Some text to rewrite')" />

        <p v-if="!instructions" class="text-sm text-gray-500 dark:text-gray-400">{{ __('Write some instructions to try it.') }}</p>
        <p v-else-if="!options.length" class="text-sm text-gray-500 dark:text-gray-400">{{ __('There are no entries with text in these collections to try it on.') }}</p>

        <p v-if="running" role="status" class="text-sm text-gray-500 dark:text-gray-400">{{ __('Trying it…') }}</p>

        <!-- What it would do, in a dashed outline as it's only a draft. Out of date (dimmed) once the instructions or what it's tried on change. -->
        <div v-else-if="result" role="status" :class="['space-y-2 rounded-lg border border-dashed border-gray-300 p-3 transition-opacity dark:border-gray-600', stale && 'opacity-50']">
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ result.summary }}</p>

            <ul v-if="result.items.length" class="divide-y divide-dashed divide-gray-300 dark:divide-gray-600">
                <li v-for="(item, i) in result.items" :key="i" class="space-y-1 py-2 last:pb-0">
                    <span v-if="item.field" class="block text-2xs font-medium text-gray-500 dark:text-gray-400">{{ item.field }}</span>
                    <!-- A finding quotes the sentence, then says what's wrong with it -->
                    <template v-if="result.kind === 'findings'">
                        <q class="block text-sm text-gray-900 dark:text-gray-200">{{ item.quote }}</q>
                        <span class="block text-sm text-gray-600 dark:text-gray-400">{{ item.note }}</span>
                    </template>
                    <!-- A change shows what it would add (yellow) and take out (struck through), as the suggestions on fields do -->
                    <span v-else class="block text-sm text-gray-900 dark:text-gray-200">
                        <template v-for="(part, p) in diffWords(item.from, item.to)" :key="p">
                            <span
                                :class="{
                                    'rounded-sm bg-yellow-100 text-gray-900 dark:bg-yellow-400/20 dark:text-yellow-100': part.type === 'added',
                                    'rounded-sm bg-red-50 text-red-700 line-through dark:bg-red-400/15 dark:text-red-300': part.type === 'removed',
                                }"
                                >{{ part.text }}</span
                            >{{ ' ' }}
                        </template>
                    </span>
                </li>
            </ul>
        </div>
    </div>
</template>
