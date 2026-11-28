<template>
    <Stack :title="__('Link')" size="narrow" inset :wrap-slot="false" v-model:open="open">
        <template #trigger>
            <Button
                class="px-2!"
                :class="{ active }"
                variant="ghost"
                size="sm"
                :aria-label="button.text"
                v-tooltip="button.text"
                @mousedown.prevent
            >
                <ui-icon :name="button.svg" class="size-3.5!" />
            </Button>
        </template>
        <LinkPicker
            v-if="linkAttrs !== null"
            :link-attrs
            :link-types="button.linkTypes"
            :link-data
            :selected-text
            markdown
            @updated="setLink"
            @canceled="open = false"
            @item-loaded="(ref, item) => (loadedLinkData[ref] = item)"
        />
    </Stack>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Button, Stack } from '@/components/ui';
import LinkPicker from '@/components/inputs/LinkPicker.vue';

const props = defineProps({
    button: { type: Object, required: true },
    editor: { type: Object, required: true },
    active: { type: Boolean, default: false },
});

const open = ref(false);
const linkAttrs = ref(null);
const selection = ref(null);
const selectedText = ref('');
const loadedLinkData = reactive({});

const linkData = computed(() => ({ ...props.button.linkData, ...loadedLinkData }));

// With nothing selected, the link needs some text. An entry or asset link would
// otherwise show its statamic:// reference, so the item's title is used, or the
// filename when there's no title, like for assets.
function textFor(href, title) {
    const reference = href.match(/^statamic:\/\/([^?#]*)/)?.[1];

    if (title || !reference) return title || href;

    return linkData.value[reference]?.title ?? reference.split(/::|\//).pop();
}

// Picking an entry or asset moves focus around, so the selection from when the
// stack opened is restored before linking it.
function setLink({ href, title }) {
    const chain = props.editor.chain().focus().setTextSelection(selection.value).extendMarkRange('link');

    if (!href) {
        chain.unsetMark('link');
    } else if (selection.value.from === selection.value.to && !linkAttrs.value.href) {
        chain.insertContent({ type: 'text', text: textFor(href, title), marks: [{ type: 'link', attrs: { href, title } }] });
    } else {
        chain.setLink({ href, title });
    }

    chain.run();
    open.value = false;
}

watch(open, (open) => {
    if (open) {
        const { from, to } = props.editor.state.selection;

        selection.value = { from, to };
        selectedText.value = props.editor.state.doc.textBetween(from, to, '');
        linkAttrs.value = props.editor.getAttributes('link');
    } else {
        linkAttrs.value = null;
        props.editor.commands.focus();
    }
});
</script>
