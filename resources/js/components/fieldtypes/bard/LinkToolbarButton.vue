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
            getMarkAttrs: this.editor.getMarkAttrs.bind(this.editor),
        }
    },

    methods: {

        toggleLinkToolbar() {            
            this.showingToolbar = ! this.showingToolbar;

            if (this.showingToolbar) {
                this.linkAttrs = this.getMarkAttrs('link');
            } else {
                this.editor.focus();
            }
        },

        setLink(attributes) {
            this.editor.commands.link(attributes);
            this.linkAttrs = null;
            this.showingToolbar = false;
            this.editor.focus();
        }

    }

}
</script>
