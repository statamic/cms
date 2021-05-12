<template>

    <slugify
        :enabled="generate"
        :from="source"
        :separator="separator"
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
        }

    },

    watch: {

        value(value) {
            this.slug = value;
        },

        slug(slug) {
            this.update(slug);
        }

    }

}
</script>
