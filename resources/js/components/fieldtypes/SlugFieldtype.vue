<template>

    <slugify
        ref="slugify"
        :enabled="generate"
        :from="source"
        :separator="separator"
        :language="language"
        v-model="slug"
    >
        <text-fieldtype
            slot-scope="{ }"
            class="font-mono text-xs"
            handle="slug"
            :config="config"
            :read-only="isReadOnly"
            v-model="slug"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />
    </slugify>

</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype],

    data() {
        return {
            slug: this.value,
            generate: this.config.generate
        }
    },

    computed: {

        separator() {
            return this.config.separator || '-';
        },

        store() {
            let store;
            let parent = this;

            while (! parent.storeName) {
                parent = parent.$parent;
                store = parent.storeName;
                if (parent === this.$root) return null;
            }

            return store;
        },

        source() {
            if (! this.generate) return;

            const field = this.config.from || 'title';

            return this.$store.state.publish[this.store].values[field];
        },

        language() {
            if (! this.store) return;
            const targetSite = this.$store.state.publish[this.store].site;
            return targetSite ? Statamic.$config.get('sites').find(site => site.handle === targetSite).lang : null;
        }

    },

    watch: {

        value(value) {
            this.slug = value;
        },

        slug(slug) {
            this.updateDebounced(slug);
        }

    },

    created() {
        this.$events.$on('localization.created', this.handleLocalizationCreated);
    },

    destroyed() {
        this.$events.$off('localization.created', this.handleLocalizationCreated);
    },

    methods: {

        handleLocalizationCreated({ store }) {
            // Only reset for the "slug" field in the matching store.
            // Other slug fields that aren't named "slug" should be left alone.
            if (this.handle === 'slug' && store === this.store) {
                this.$refs.slugify.reset();
            }
        }

    }

}
</script>
