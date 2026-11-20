<script>
import { reactive } from 'vue';
// The fields whose changes are open, shared by the label and the parts in and under the field
const comparing = reactive(new Set());
// The fields whose changes are shown in the field, so the part below stays empty. Only the part in the field can tell, so it's shared.
const inFields = reactive(new Set());
</script>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { Badge, Icon } from '@ui';
import AiSuggestBlocks from '@/components/ai-suggest/AiSuggestBlocks.vue';
import diffWords from '@/components/ai-suggest/diff-words.js';
import { editorIn, proseClassOf, textBoxIn, textStyleOf } from '@/components/ai-suggest/field-overlay.js';
import { blocksOf } from '@/components/ai-suggest/mock-bard-suggestions.js';
import useAiChanges from '@/composables/use-ai-changes.js';
import { linkDetails, linkTooltip } from './mock-runs.js';

// Prototype: what a field the AI chat changed shows. Next to the label (part="label"), the badge in the theme's AI colour, which shows what
// changed, word by word, in the field (part="field"), and Undo, which puts back what was there. A field without a text box of its own, or
// Bard's editor, shows them under it instead (part="below").
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

// A field filled in from nothing is "added", not "changed". Its badge is plain grey with a plus, as a label: the purple one opens a diff, and a
// purple badge with nothing to open would look like it should.
const addedBadge = 'bg-gray-100 border-gray-400 text-gray-800 dark:border dark:bg-gray-700 dark:border-gray-500 dark:text-gray-100';

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

// In the field: over a text box, the changes are laid over its text, styled like it (as the suggestions' preview is, see AiSuggest.vue).
// In Bard, they take the editor's place, block by block, in its own type.
const anchor = useTemplateRef('anchor');
const box = ref(null);
const boxStyle = ref({});
const bard = ref(null);
const proseClass = ref('');

function measure() {
    const wrapper = anchor.value?.parentElement;
    const prose = editorIn(wrapper);
    // A text box of the field's own, not one inside a Bard set or the like
    const field = wrapper?.querySelector('.bard-fieldtype, .replicator-fieldtype, .grid-fieldtype') ? null : textBoxIn(wrapper);
    bard.value = prose;
    box.value = field;
    if (prose) proseClass.value = proseClassOf(prose);
    if (field) boxStyle.value = textStyleOf(field);
}

watch(open, () => props.part === 'field' && nextTick(measure), { immediate: true });

const inField = computed(() => !!box.value || !!bard.value);
const showBelow = computed(() => !inFields.has(key.value));
watch(inField, (has) => props.part === 'field' && (has ? inFields.add(key.value) : inFields.delete(key.value)), { immediate: true });

// Bard's editor is hidden while its changes are showing in its place
const showingInBard = computed(() => props.part === 'field' && open.value && !hadNothing.value && !!bard.value);
watch(showingInBard, (showing) => {
    if (bard.value) bard.value.style.display = showing ? 'none' : '';
});
onBeforeUnmount(() => {
    if (showingInBard.value && bard.value) bard.value.style.display = '';
    if (props.part === 'field') inFields.delete(key.value);
});

// Bard's blocks before and after, lined up: a block the AI kept, however reworded, pairs with the one it became (they share most of their
// words); one it cut has nothing after it, and one it added has nothing before it. In the shape AiSuggestBlocks shows.
const words = (text) => new Set(text.toLowerCase().match(/[\p{L}\p{N}']+/gu) ?? []);
function alike(a, b) {
    const x = words(a);
    const y = words(b);
    if (!x.size || !y.size) return false;
    let shared = 0;
    x.forEach((word) => y.has(word) && shared++);
    return shared / Math.min(x.size, y.size) >= 0.5;
}

const bardChanges = computed(() => {
    if (!change.value || !bard.value) return [];
    const before = blocksOf(change.value.before);
    const after = blocksOf(change.value.after);
    const rows = [];
    let j = 0;
    before.forEach((block) => {
        // Anything added before this block's match comes first
        const match = after.findIndex((next, k) => k >= j && next.type === block.type && alike(block.text, next.text));
        if (match === -1) return rows.push({ ...block, before: block.text, after: null, changed: true });
        for (; j < match; j++) rows.push({ ...after[j], before: '', after: after[j].text, changed: true });
        rows.push({ ...block, before: block.text, after: after[j].text, changed: block.text !== after[j].text });
        j++;
    });
    for (; j < after.length; j++) rows.push({ ...after[j], before: '', after: after[j].text, changed: true });
    return rows.map((row, index) => ({ ...row, index }));
});

const partClass = (type) => ({
    'rounded-sm bg-yellow-100 dark:bg-transparent dark:text-yellow-300': ['added', 'linked'].includes(type),
    'underline underline-offset-2': type === 'linked',
    'rounded-sm bg-red-50 text-red-700 line-through dark:bg-transparent dark:text-red-400': type === 'removed',
});
</script>

<template>
    <template v-if="change">
        <!-- Next to the label. Undo is set like the Read Only note (small grey text after an icon), with a hover background as it's a button. It sits at the top of the label's line, which it fills exactly (20px), so it doesn't make the label taller, as aligning it to the middle did. -->
        <span v-if="part === 'label'" class="ms-2 inline-flex items-center gap-1.5 align-top text-2xs font-normal text-gray-500 dark:text-gray-400">
            <!-- On a field that was empty, there's nothing to compare, so the badge is just a label, with no chevron -->
            <Badge v-if="hadNothing" size="sm" icon="plus" :class="addedBadge" :text="__('Added by AI')" />
            <!-- Otherwise it opens and closes the changes, with a chevron that turns over while they're open -->
            <button v-else type="button" class="group/badge cursor-pointer" :aria-expanded="open" :aria-label="open ? __('Hide changes') : __('Show what the AI changed')" @click="open = !open">
                <!-- Dark mode is a purple tint rather than the grey the other Badge colours have, as the grey lost the purple: a 20% fill of the theme's AI colour, with its lightened version for the text. -->
                <Badge
                    size="sm"
                    icon-append="chevron-down"
                    :class="['bg-ai/5 border-ai/40 text-ai group-hover/badge:bg-ai/10 dark:bg-ai/20 dark:text-ai-light dark:group-hover/badge:bg-ai/30 [&_svg]:transition-transform', open ? '[&_svg]:rotate-180' : '']"
                    :text="__('Changed by AI')"
                />
            </button>
            <button type="button" class="cursor-pointer rounded-md px-1 py-0.5 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white" @click="aiChanges.undo(container, path)">
                <Icon name="undo" class="inline size-3! -mt-0.5 me-0.5 align-middle" />
                {{ __('Undo') }}
            </button>
        </span>

        <!-- Under the field, while the badge has it open, for a field with no text box of its own: just the changes, a little smaller than the field's text, with no box -->
        <p v-else-if="part === 'below' && open && !hadNothing && showBelow" class="mt-1.5 px-1 text-xs/5 text-gray-700 dark:text-gray-300">
            <span v-for="(p, i) in parts" :key="i" v-tooltip="p.type === 'linked' && linkDetails(p.text) ? linkTooltip(linkDetails(p.text)) : null" :class="partClass(p.type)">{{ p.text }}</span>
        </p>

        <!-- In the field, while the badge has it open: laid over its text box, growing taller than it if the cut words need the room -->
        <template v-else-if="part === 'field'">
            <span ref="anchor" hidden />
            <div
                v-if="open && !hadNothing && box"
                aria-hidden="true"
                :style="boxStyle"
                class="pointer-events-none absolute inset-x-0 top-0 z-(--z-index-above) min-h-full overflow-hidden border-solid border-gray-300 bg-white whitespace-pre-wrap antialiased dark:border-white/10 dark:bg-gray-900"
            >
                <span v-for="(p, i) in parts" :key="i" :class="partClass(p.type)">{{ p.text }}</span>
            </div>
        </template>

        <!-- In Bard, in the editor's place, with its classes so it takes the field's own type and spacing -->
        <Teleport v-if="showingInBard" :to="bard.parentElement">
            <div data-ai-preview :class="proseClass">
                <AiSuggestBlocks :changes="bardChanges" />
            </div>
        </Teleport>
    </template>
</template>
