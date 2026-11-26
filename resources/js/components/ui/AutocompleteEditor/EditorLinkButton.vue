<template>
    <Popover v-model:open="open" class="w-80">
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
        <form class="space-y-3" @submit.prevent="save">
            <Input v-model="href" :aria-label="__('URL')" placeholder="https://" focus />
            <div class="flex justify-end gap-2">
                <Button v-if="active" size="sm" variant="ghost" :text="__('Remove Link')" @click="remove" />
                <Button type="submit" size="sm" variant="primary" :text="__('Apply')" />
            </div>
        </form>
    </Popover>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Button, Input, Popover } from '@/components/ui';

const props = defineProps({
    button: { type: Object, required: true },
    editor: { type: Object, required: true },
    active: { type: Boolean, default: false },
});

const open = ref(false);
const href = ref('');

// Emails are read away from the site, so a link without a scheme, like
// "example.com/brochure.pdf", is treated as a website rather than a relative path.
function normalize(url) {
    url = url.trim();

    return !url || /^[a-z][a-z\d+.-]*:/i.test(url) ? url : `https://${url}`;
}

function save() {
    const url = normalize(href.value);

    if (!url) return remove();

    const chain = props.editor.chain().focus().extendMarkRange('link');

    if (props.editor.state.selection.empty && !props.active) {
        chain.insertContent({ type: 'text', text: url, marks: [{ type: 'link', attrs: { href: url } }] });
    } else {
        chain.setLink({ ...props.editor.getAttributes('link'), href: url });
    }

    chain.run();
    open.value = false;
}

function remove() {
    props.editor.chain().focus().extendMarkRange('link').unsetMark('link').run();
    open.value = false;
}

watch(open, (open) => {
    if (open) href.value = props.editor.getAttributes('link').href ?? '';
});
</script>
