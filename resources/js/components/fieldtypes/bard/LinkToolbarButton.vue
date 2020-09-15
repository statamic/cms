<template>

    <div class="inline-block relative">

        <button
            class="bard-toolbar-button"
            :class="{ active }"
            v-html="button.html"
            v-tooltip="button.text"
            :aria-label="button.text"
            @click="showLinkToolbar(getMarkAttrs('link'))"
        />

        <link-toolbar
            v-if="showingToolbar"
            :initial-link-attrs="linkAttrs"
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

        showLinkToolbar(attrs) {
            this.showingToolbar = false;
            this.$nextTick(() => {
                this.showingToolbar = true;
                this.linkAttrs = attrs;
            });
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
