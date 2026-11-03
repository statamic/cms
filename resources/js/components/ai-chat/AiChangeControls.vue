<script>
import { reactive } from 'vue';
// The fields whose changes are open, shared by the label and the part under the field
const comparing = reactive(new Set());
</script>

<script setup>
import { computed } from 'vue';
import { Badge, Icon } from '@ui';
import diffWords from '@/components/ai-suggest/diff-words.js';
import useAiChanges from '@/composables/use-ai-changes.js';

// Prototype: what a field the AI chat changed shows. Next to the label (part="label"), the badge in the theme's AI colour, which shows what
// changed, word by word, under the field (part="below"), and Undo, which puts back what was there.
const props = defineProps({
    container: { type: String, required: true },
    path: { type: String, required: true },
    part: { type: String, required: true },
});

const aiChanges = useAiChanges();
const change = computed(() => aiChanges.changeFor(props.container, props.path));
const key = computed(() => `${props.container}:${props.path}`);
const open = computed({ get: () => comparing.has(key.value), set: (value) => (value ? comparing.add(key.value) : comparing.delete(key.value)) });

// The words of a value: text as it is, and Bard's content as its text
function wordsOf(value) {
    if (typeof value === 'string') return value;
    if (!Array.isArray(value)) return '';
    const walk = (nodes) => nodes.map((node) => (node.type === 'text' ? node.text : walk(node.content ?? []))).join('');
    return value.map((node) => walk(node.content ?? [])).join(' ');
}
// The bits of Bard's content that are links
function linksOf(value) {
    if (!Array.isArray(value)) return [];
    const found = [];
    const walk = (nodes) => nodes.forEach((node) => (node.type === 'text' ? node.marks?.some((mark) => mark.type === 'link') && found.push(node.text) : walk(node.content ?? [])));
    walk(value);
    return found;
}

// A field that was empty (like on a new entry) has nothing to compare with, so its badge has no changes to open: it's just a label
const hadNothing = computed(() => !!change.value && !wordsOf(change.value.before ?? '').trim());

// What changed, word by word, in the same yellow and red as the suggestions. When only links were added (the words are the same), the
// linked words are marked as added instead.
const parts = computed(() => {
    if (!change.value) return [];
    const before = wordsOf(change.value.before ?? '');
    const after = wordsOf(change.value.after ?? '');
    if (before !== after) return diffWords(before, after).map((part) => ({ ...part, text: part.text + ' ' }));
    const links = linksOf(change.value.after);
    if (!links.length) return [{ text: after, type: 'same' }];
    const pattern = new RegExp(`(${links.map((link) => link.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|')})`);
    return after.split(pattern).filter(Boolean).map((text) => ({ text, type: links.includes(text) ? 'linked' : 'same' }));
});

const partClass = (type) => ({
    'rounded-sm bg-yellow-100 dark:bg-yellow-400/20 dark:text-yellow-100': ['added', 'linked'].includes(type),
    'underline underline-offset-2': type === 'linked',
    'rounded-sm bg-red-50 text-red-700 line-through dark:bg-red-400/15 dark:text-red-300': type === 'removed',
});
</script>

<template>
    <template v-if="change">
        <!-- Next to the label. Undo is set like the Read Only note (small grey text after an icon), with a hover background as it's a button. -->
        <span v-if="part === 'label'" class="ms-2 inline-flex items-center gap-1.5 align-middle text-2xs font-normal text-gray-500 dark:text-gray-400">
            <!-- On a field that was empty, there's nothing to compare, so the badge is just a label, with no chevron -->
            <Badge v-if="hadNothing" size="sm" class="bg-ai/5 border-ai/40 text-ai dark:border dark:bg-gray-800 dark:border-ai/60 dark:text-white" :text="__('Changed by AI')" />
            <!-- Otherwise it opens and closes the changes, with a chevron that turns over while they're open -->
            <button v-else type="button" class="group/badge cursor-pointer" :aria-expanded="open" :aria-label="open ? __('Hide changes') : __('Show what the AI changed')" @click="open = !open">
                <Badge
                    size="sm"
                    icon-append="chevron-down"
                    :class="['bg-ai/5 border-ai/40 text-ai group-hover/badge:bg-ai/10 dark:border dark:bg-gray-800 dark:border-ai/60 dark:text-white [&_svg]:transition-transform', open ? '[&_svg]:rotate-180' : '']"
                    :text="__('Changed by AI')"
                />
            </button>
            <button type="button" class="cursor-pointer rounded-md px-1 py-0.5 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white" @click="aiChanges.undo(container, path)">
                <Icon name="undo" class="inline size-3! -mt-0.5 me-0.5 align-middle" />
                {{ __('Undo') }}
            </button>
        </span>

        <!-- Under the field, while the badge has it open: just the changes, a little smaller than the field's text, with no box -->
        <p v-else-if="part === 'below' && open && !hadNothing" class="mt-1.5 px-1 text-xs/5 text-gray-700 dark:text-gray-300">
            <span v-for="(p, i) in parts" :key="i" :class="partClass(p.type)">{{ p.text }}</span>
        </p>
    </template>
</template>
