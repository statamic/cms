<template>

    <div class="inline-block relative">

        <button
            class="bard-toolbar-button"
            :class="{ active }"
            v-html="button.html"
            v-tooltip="button.text"
            :aria-label="button.text"
            @click="toggleLinkToolbar"
        />

        <link-toolbar
            v-if="showingToolbar"
            :link-attrs="linkAttrs"
            :config="config"
            :bard="bard"
            @updated="setLink"
            @deselected="showingToolbar = false"
        />
    </div>

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
                this.editor.view.dom.focus();
            }
        },

        setLink(attributes) {
            this.editor.commands.setLink(attributes);
            this.linkAttrs = null;
            this.showingToolbar = false;
            this.editor.view.dom.focus();
        }

    }

}
</script>
