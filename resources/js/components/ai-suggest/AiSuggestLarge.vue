<script setup>
import { computed, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { Button } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';
import AiSuggestBlocks from './AiSuggestBlocks.vue';
import AiSuggestList from './AiSuggestList.vue';
import { changesOf, suggestForBard, toBard } from './mock-bard-suggestions.js';
import { icon } from './use-ai-suggest.js';
import useSidebarCard, { cardEntrance, onSuggestButton } from './use-sidebar-card.js';
import { editorIn, proseClassOf } from './field-overlay.js';

// Prototype: suggestions for a large field, like Bard. A whole rewrite is too long for the small dropdown the other fields use (see AiSuggest.vue),
// so they open over the entry's sidebar (the column with Published and the slug), where there's room for all of each one. They behave like
// that dropdown, though: clicking anywhere else, or Esc, closes them.
const props = defineProps({
    config: { type: Object, required: true },
    value: { default: null },
    values: { type: Object, default: () => ({}) },
    buttonClass: { type: [String, Array, Object], default: null },
});

const emit = defineEmits(['apply', 'preview']);

const open = ref(false);
const loading = ref(false);
const suggestions = ref([]);
// What was in the field when the suggestions were made
const original = ref([]);
let timer = null;

// Prototype: "think" for a moment, then show the mock suggestions. A label comes from the "Make it…" box, which (for now) only renames them.
function generate(label = null) {
    loading.value = true;
    clearTimeout(timer);
    timer = setTimeout(() => {
        original.value = props.value;
        suggestions.value = suggestForBard(props.value, label);
        loading.value = false;
    }, 900);
}

const changes = computed(() => suggestions.value.map((suggestion) => changesOf(original.value, suggestion)));

function toggled(isOpen) {
    open.value = isOpen;
    if (isOpen) generate();
}

function apply(index) {
    preview(null);
    emit('apply', toBard(suggestions.value[index].blocks));
    open.value = false;
}

// Hovering or focusing a suggestion shows it in the field in place of what's there, as the other fields do: just the text it would leave,
// in the field's own type, without its changes marked. Moving off it puts the field back. Nothing changes until you pick one.
// While it's previewing, the field's label gets a purple "Previewing Tighter" badge, like its "Changed by AI" one (see Field.vue).
const previewing = ref(null);
const previewLabel = computed(() => (previewing.value === null ? null : suggestions.value[previewing.value]?.label));
watch(previewLabel, (label) => emit('preview', label));
const previewTarget = ref(null);
const proseClass = ref('');
const editorHeight = ref(null);
let hidden = null;

function preview(index) {
    if (index === previewing.value) return;
    const prose = editorIn(anchor.value?.parentElement);

    // Put the field back first
    if (hidden) hidden.style.display = '';
    hidden = null;
    previewing.value = index;
    previewTarget.value = null;
    if (index === null || !prose) return;

    // Shown in the editor's place, with its classes, so it takes the field's own type and spacing. It's never shorter than the editor was.
    proseClass.value = proseClassOf(prose);
    editorHeight.value = `${prose.offsetHeight}px`;
    previewTarget.value = prose.parentElement;
    hidden = prose;
    prose.style.display = 'none';
}

// Only what each suggestion keeps, as it reads
const previewBlocks = computed(() =>
    changes.value.map((blocks) => blocks.filter((change) => change.after !== null).map((change) => ({ ...change, before: change.after, changed: false }))),
);

watch(open, (isOpen) => !isOpen && preview(null));

const follow = (text) => generate(text.trim().replace(/^./, (letter) => letter.toUpperCase()));

// The floating AI chat sits over the same corner, so it's put away while the suggestions are open. Bringing it back (with Ask or its shortcut)
// puts the suggestions away instead. A docked chat has its own column, so it's left alone.
const chat = useAiChat();
watch(open, (isOpen) => isOpen && chat.floating.value && chat.close());
watch(chat.open, (chatOpen) => chatOpen && (open.value = false));

// Over the entry's sidebar (see use-sidebar-card.js)
const anchor = useTemplateRef('anchor');
const button = useTemplateRef('button');
const panel = useTemplateRef('panel');
const { place, fit, scroller, switched } = useSidebarCard(open, anchor, () => (open.value = false), panel);

// Like a dropdown: clicking anywhere else, or Esc, closes it. The suggest button toggles it itself.
function onPointerDown(event) {
    if (panel.value?.contains(event.target) || button.value?.$el?.contains(event.target) || onSuggestButton(event)) return;
    open.value = false;
}

function onKeydown(event) {
    if (event.key !== 'Escape') return;
    open.value = false;
    button.value?.$el?.focus();
}

watch(open, (isOpen) => {
    document[isOpen ? 'addEventListener' : 'removeEventListener']('pointerdown', onPointerDown);
    document[isOpen ? 'addEventListener' : 'removeEventListener']('keydown', onKeydown);
});

onBeforeUnmount(() => {
    preview(null);
    document.removeEventListener('pointerdown', onPointerDown);
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <span ref="anchor" hidden />

    <!-- The suggest button at the end of Bard's toolbar. It stays highlighted, with a purple icon, while the suggestions are open. -->
    <Button
        ref="button"
        data-ai-suggest-button
        size="xs"
        :variant="open ? 'default' : 'ghost'"
        :icon="icon"
        class="[&_svg]:size-3.5! [&_svg]:opacity-100!"
        :class="[
            buttonClass,
            open ? '[&_svg]:text-ai! dark:[&_svg]:text-ai-light!' : '[&_svg]:text-gray-400! dark:[&_svg]:text-gray-500!',
        ]"
        :aria-label="__('Suggest')"
        :aria-expanded="open"
        v-tooltip="__('Suggest')"
        @click="toggled(!open)"
    />

    <!-- Over the entry's sidebar, styled like a dropdown (shadow, a quick fade and zoom in). It sticks 16px below the top of the scrolling area,
         and is never taller than fits on screen (see use-sidebar-card.js). The wrapper lets clicks through to the page below the card. -->
    <!-- Without a sidebar to open over (a narrow window, or no sidebar in the blueprint), it opens under the field instead, like a dropdown -->
    <Teleport v-if="open" :to="scroller || 'body'" :disabled="!place">
        <div :class="place ? 'pointer-events-none absolute z-(--z-index-modal)' : 'absolute top-full z-(--z-index-modal) mt-2 inset-x-0'" :style="place">
            <div :class="place ? 'pointer-events-auto sticky top-4 flex flex-col' : 'flex max-h-[60vh] flex-col'" :style="place && fit">
                <div
                    ref="panel"
                    class="flex min-h-0 flex-col overflow-hidden"
                    :class="['rounded-xl border border-gray-200 bg-white shadow-lg dark:border-white/10 dark:bg-gray-800', cardEntrance(switched)]"
                >
                    <AiSuggestList
                        :suggestions
                        :changes
                        :loading
                        :field="__(config.display)"
                        list-class="min-h-0 flex-1"
                        @apply="apply"
                        @preview="preview"
                        @follow="follow"
                        @retry="generate()"
                        @close="open = false"
                    />
                </div>
            </div>
        </div>
    </Teleport>

    <!-- The hovered suggestion, in the editor's place (see preview) -->
    <Teleport v-if="previewTarget && previewing !== null" :to="previewTarget">
        <!-- All the suggestions are laid in one spot, with only the hovered one showing, so the field is as tall as the tallest of them (and at
             least as tall as the editor was). Moving between them doesn't change its height, so the page doesn't jump about. -->
        <div data-ai-preview :class="proseClass" :style="{ minHeight: editorHeight }" class="grid! content-start">
            <div v-for="(blocks, i) in previewBlocks" :key="i" class="[grid-area:1/1]" :class="i !== previewing && 'invisible'" :aria-hidden="i !== previewing">
                <AiSuggestBlocks :changes="blocks" />
            </div>
        </div>
    </Teleport>

</template>
