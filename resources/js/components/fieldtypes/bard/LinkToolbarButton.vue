<template>
    <Popover ref="popover" class="!w-84" :inset="true" v-model:open="showingToolbar">
        <template #trigger>
            <Button
                class="px-2! [&_svg]:size-3.5"
                :class="{ active }"
                variant="ghost"
                size="sm"
                :aria-label="button.text"
                v-tooltip="button.text"
            >
                <svg-icon :name="button.svg" v-if="button.svg" class="size-4" />
                <div class="flex items-center" v-html="button.html" v-if="button.html" />
            </Button>
        </template>
        <link-toolbar
            v-if="linkAttrs !== null"
            class="w-84"
            ref="toolbar"
            :link-attrs="linkAttrs"
            :config="config"
            :bard="bard"
            @updated="setLink"
            @canceled="close"
        />
    </Popover>
</template>

<script>
import { Popover } from '@statamic/ui';
import LinkToolbar from './LinkToolbar.vue';
import BardToolbarButton from './ToolbarButton.vue';

export default {
    mixins: [BardToolbarButton],

    components: {
        Popover,
        LinkToolbar,
    },

    data() {
        return {
            linkAttrs: null,
            showingToolbar: false,
        };
    },

    methods: {
        close() {
            this.showingToolbar = false;
            this.$refs.popover.close();
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
                this.linkAttrs = this.editor.getAttributes('link');
            } else {
                this.editor.commands.focus();
                this.linkAttrs = null;
            }
        }
    },

    created() {
        this.bard.events.on('link-toggle', () => {
            this.toggleLinkToolbar();
            this.$refs.popover.toggle();
        });
    },

    beforeUnmount() {
        this.bard.events.off('link-toggle');
    },
};
</script>
