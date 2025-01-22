<template>
    <popover ref="popover" placement="bottom-end" @closed="popoverClosed" :clickaway="false">
        <template #trigger>
            <button
                class="bard-toolbar-button"
                :class="{ active }"
                v-tooltip="button.text"
                :aria-label="button.text"
                @click="toggleLinkToolbar"
            >
                <svg-icon :name="button.svg" v-if="button.svg"></svg-icon>
            </button>
        </template>
        <template #default>
            <link-toolbar
                class="w-84"
                ref="toolbar"
                v-if="showingToolbar"
                :link-attrs="linkAttrs"
                :config="config"
                :bard="bard"
                @updated="setLink"
                @canceled="close"
            />
        </template>
    </popover>
</template>

<script>
import LinkToolbar from './LinkToolbar.vue';

export default {

    mixins: [BardToolbarButton],

    components: {
        LinkToolbar,
    },

    data() {
        return {
            linkAttrs: null,
            showingToolbar: false,
        }
    },

    methods: {

        toggleLinkToolbar() {
            this.showingToolbar = ! this.showingToolbar;

            if (this.showingToolbar) {
                this.linkAttrs = this.editor.getAttributes('link');
            } else {
                this.editor.commands.focus();
            }
        },

        close() {
            this.showingToolbar = false;
            this.$refs.popover.close();
        },

        popoverClosed() {
            this.showingToolbar = false;
        },

        setLink(attributes) {
            this.editor.chain().focus().setLink(attributes).run();
            this.linkAttrs = null;
            this.close();
        }
    },

    created() {
        this.bard.$on('link-toggle', () => {
            this.toggleLinkToolbar();
            this.$refs.popover.toggle();
        });
    },

    beforeUnmount() {
        this.bard.$off('link-toggle');
    }
}
</script>
