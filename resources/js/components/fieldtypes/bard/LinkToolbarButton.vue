<template>
    <Stack
        :title="__('Link')"
        size="narrow"
        inset
        :wrap-slot="false"
        v-model:open="showingToolbar"
    >
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
                <ui-icon :name="button.svg" v-if="button.svg" class="size-4" />
                <div class="flex items-center" v-html="button.html" v-if="button.html" />
            </Button>
        </template>
        <LinkPicker
            v-if="linkAttrs !== null"
            :link-attrs="linkAttrs"
            :config="config"
            :link-types="bard.meta.linkTypes"
            :link-data="bard.meta.linkData"
            :selected-text="selectedText"
            @updated="setLink"
            @canceled="close"
            @item-loaded="putItemDataIntoMeta"
        />
    </Stack>
</template>

<script>
import { Stack } from '@/components/ui';
import LinkPicker from '@/components/inputs/LinkPicker.vue';
import BardToolbarButton from './ToolbarButton.vue';

export default {
    mixins: [BardToolbarButton],

    components: {
        LinkPicker,
        Stack,
    },

    data() {
        return {
            linkAttrs: null,
            selectedText: '',
            showingToolbar: false,
        };
    },

    methods: {
        close() {
            this.showingToolbar = false;
        },

        putItemDataIntoMeta(ref, item) {
            let meta = this.bard.meta;
            meta.linkData[ref] = item;
            this.bard.updateMeta(meta);
        },

        setLink(attributes) {
            this.editor.chain().focus().setLink(attributes).run();
            this.linkAttrs = null;
            this.close();
        },
    },

    watch: {
        showingToolbar(showingToolbar) {
            if (showingToolbar) {
                const { from, to } = this.editor.state.selection;

                this.selectedText = this.editor.state.doc.textBetween(from, to, '');
                this.linkAttrs = this.editor.getAttributes('link');
            } else {
                this.editor.commands.focus();
                this.linkAttrs = null;
            }
        }
    },

    created() {
        this.bard.events.on('open-link-toolbar', () => this.showingToolbar = true);
        this.bard.events.on('link-selected', (attrs) => {
            if (this.linkAttrs !== null) this.linkAttrs = attrs;
        });
    },

    beforeUnmount() {
        this.bard.events.off('open-link-toolbar');
        this.bard.events.off('link-selected');
    },

};
</script>
