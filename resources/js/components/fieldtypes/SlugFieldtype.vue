<template>
    <slugify
        ref="slugify"
        :enabled="generate"
        :from="source"
        :to="slug"
        :separator="separator"
        :language="language"
        :async="config.async"
        @slugifying="syncing = true"
        @slugified="syncing = false; slug = $event"
    >
        <div>
            <text-input
                v-model="slug"
                classes="font-mono text-xs"
                :isReadOnly="isReadOnly"
                :append="config.show_regenerate"
                :name="slug"
                :id="fieldId"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
                direction="ltr"
            >
                <template v-slot:append v-if="config.show_regenerate">
                    <button class="input-group-append items-center flex" @click="sync" v-tooltip="__('Regenerate from: :field', { 'field': config.from })">
                        <svg-icon name="light/synchronize" class="w-5 h-5" v-show="!syncing" />
                        <div class="w-5 h-5" v-show="syncing"><loading-graphic inline text="" class="mt-0.5 ml-0.5" /></div>
                    </button>
                </template>
            </text-input>
        </div>
    </slugify>

</template>

<script>
import { data_get } from '../../bootstrap/globals';
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    data() {
        return {
            slug: this.value,
            generate: this.config.generate,
            syncing: false,
        }
    },

    computed: {

        separator() {
            return this.config.separator || '-';
        },

        store() {
            return this.storeName;
        },

        source() {
            if (! this.generate) return;

            const field = this.config.from || 'title';
            let key = field;

            if (this.fieldPathPrefix) {
                let dottedPrefix = this.fieldPathPrefix.replace(new RegExp('\.'+this.handle+'$'), '');
                key = dottedPrefix + '.' + field;
            }

            return data_get(this.$store.state.publish[this.store].values, key);
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

    mounted() {
        if (this.config.required && !this.value) this.update(this.$refs.slugify.slug);
    },

    methods: {

        handleLocalizationCreated({ store }) {
            // Only reset for the "slug" field in the matching store.
            // Other slug fields that aren't named "slug" should be left alone.
            if (this.handle === 'slug' && store === this.store) {
                this.$refs.slugify.reset();
            }
        },

        sync() {
            this.$refs.slugify.reset();
        }
    }

}
</script>
