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
            >
                <ui-icon :name="button.svg" v-if="button.svg" class="size-4" />
                <div class="flex items-center" v-html="button.html" v-if="button.html" />
            </Button>
        </template>
        <link-toolbar
            v-if="linkAttrs !== null"
            ref="toolbar"
            :link-attrs="linkAttrs"
            :config="config"
            :bard="bard"
            @updated="setLink"
            @canceled="close"
        />
    </Stack>
</template>

<script>
import { Stack } from '@/components/ui';
import LinkToolbar from './LinkToolbar.vue';
import BardToolbarButton from './ToolbarButton.vue';

export default {
    mixins: [BardToolbarButton],

    components: {
        LinkToolbar,
        Stack,
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

};
</script>
