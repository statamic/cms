<template>
    <ul :class="classes">
        <branch v-for="page in pages"
                :branch-index="$index"
                :uuid="page.id"
                :title="page.title || page.slug"
                :url="buildUrl(page.slug)"
                :published="page.published"
                :edit-url="page.edit_url"
                :has-entries="page.has_entries"
                :entries-url="page.entries_url"
                :create-entry-url="page.create_entry_url"
                :child-pages="page.items"
                :collapsed.sync="page.collapsed"
                :depth="depth"
        ></branch>
    </ul>
</template>

<script>
export default {

    props: {
        pages: Array,
        depth: Number,
        parentUrl: {
            type: String,
            default: ''
        },
        collapsed: Boolean
    },

    computed: {
        classes: function () {
            // Start with the static ones
            var classes = ['branches'];

            // Add depth
            classes.push('depth-' + this.depth);

            // Empty
            if (!this.pages.length) {
                classes.push('empty');
            }

            // State
            var state = (this.collapsed) ? 'collapsed' : 'open';
            classes.push('branches-' + state);

            return classes.join(' ');
        }
    },

    methods: {

        toggle: function(page) {
            page.$set('collapsed', !page.collapsed);
        },

        buildUrl: function(slug) {
            return this.parentUrl + '/' + slug;
        }

    }

};
</script>