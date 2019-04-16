<template>

    <div class="inline-block relative">

        <button
            :class="{ active }"
            v-html="button.html"
            v-tooltip="button.text"
            @click="showLinkToolbar(getMarkAttrs('link'))"
        />

        <link-toolbar
            v-if="showingToolbar"
            :link-attrs="linkAttrs"
            :config="config"
            @updated="setLink"
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
