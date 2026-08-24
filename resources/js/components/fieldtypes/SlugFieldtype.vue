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
        @slugified="
            syncing = false;
            slug = $event;
        "
    >
        <Input
            v-model="slug"
            :id="fieldId"
            :read-only="isReadOnly"
            :name="slug"
            :disabled="config.disabled"
            :input-attrs="{ dir: contentDirection }"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        >
            <template #append v-if="config.show_regenerate">
                <Button
                    icon="sync"
                    size="sm"
                    variant="ghost"
                    @click="sync"
                    :loading="syncing"
                    v-tooltip="__('Regenerate from: :field', { field: config.from })"
                />
            </template>
        </Input>
    </slugify>
</template>

<script>
import { data_get } from '../../bootstrap/globals';
import Fieldtype from './Fieldtype.vue';
import { Input, Button, Icon } from '@/components/ui';
import { useContentDirection } from '@/composables/content-direction';

export default {
    mixins: [Fieldtype],

    components: {
        Input,
        Button,
        Icon,
    },

    setup() {
        const { direction: contentDirection } = useContentDirection();

        return { contentDirection };
    },

    data() {
        return {
            slug: this.value,
            generate: this.config.generate,
            syncing: false,
        };
    },

    computed: {
        separator() {
            return this.config.separator || '-';
        },

        source() {
            if (!this.generate) return;

            const from = this.valueFrom(this.config.from || 'title', { relative: true });

            if (!from) return from;

            const prefix = this.config.prefix_from
                ? this.valueFrom(this.config.prefix_from, { relative: false })
                : null;

            if (!prefix) return from;

            return `${prefix} ${from}`;
        },

        language() {
            if (!this.publishContainer) return;
            const targetSite = this.publishContainer.site;
            return targetSite ? Statamic.$config.get('sites').find((site) => site.handle === targetSite).lang : null;
        },
    },

    watch: {
        value(value) {
            this.slug = value;
        },

        slug(slug) {
            this.updateDebounced(slug);
        },
    },

    created() {
        this.$events.$on('localization.created', this.handleLocalizationCreated);
    },

    unmounted() {
        this.$events.$off('localization.created', this.handleLocalizationCreated);
    },

    mounted() {
        if (this.config.required && !this.value) this.update(this.$refs.slugify.slug);
    },

    methods: {
        handleLocalizationCreated({ container }) {
            // Only reset for the "slug" field in the matching container.
            // Other slug fields that aren't named "slug" should be left alone.
            if (this.handle === 'slug' && container.name === this.publishContainer.name && this.config.localizable) {
                this.$refs.slugify.reset();
            }
        },

        valueFrom(field, { relative }) {
            let key = field;

            if (relative && this.fieldPathPrefix) {
                let dottedPrefix = this.fieldPathPrefix.replace(new RegExp('\\.' + this.handle + '$'), '');
                key = dottedPrefix + '.' + field;
            }

            return data_get(this.publishContainer?.values, key);
        },

        sync() {
            this.$refs.slugify.reset();
        },
    },
};
</script>
