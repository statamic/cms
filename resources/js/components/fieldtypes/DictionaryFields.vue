<template>
    <publish-container
        name="dictionary-fields"
        :blueprint="blueprint"
        :values="value"
        :meta="publishMeta"
        :is-config="true"
        :errors="errors"
        @updated="update"
    >
        <publish-fields
            slot-scope="{ setFieldValue, setFieldMeta }"
            :fields="fields"
            @updated="setFieldValue"
            @meta-updated="setFieldMeta"
        />
    </publish-container>
</template>

<script>
import Fieldtype from "./Fieldtype.vue";

export default {
    mixins: [Fieldtype],

    inject: ['storeName'],

    computed: {
        dictionary() {
            return this.value?.type;
        },

        fields() {
            return this.meta.type.fields.concat(this.meta.dictionaries[this.dictionary]?.fields || [])
        },

        blueprint() {
            return {
                tabs: [{
                    fields: this.fields
                }]
            }
        },

        publishMeta() {
            return {
                ...this.meta.type.meta,
                ...this.meta.dictionaries[this.dictionary]?.meta
            }
        },

        errors() {
            const state = this.$store.state.publish[this.storeName];

            if (! state) {
                return {};
            }

            let errors = {}

            // Filter errors to only include those for this field, and remove the field path prefix
            // if there is one, then append it to the errors object.
            Object.entries(state.errors)
                .filter(([key, value]) => key.startsWith(this.fieldPathPrefix || this.handle))
                .forEach(([key, value]) => {
                    errors[key.split('.').pop()] = value
                })

            return errors
        },
    },

    watch: {
        dictionary() {
            this.update({
                type: dictionary,
                ...this.meta.dictionaries[this.dictionary]?.defaults
            })
        },
    },
}
</script>

<style>
.dictionary_fields-fieldtype {
    @apply p-0;

    .publish-fields {
        @apply w-full;
    }

    .config-field {
        @apply md:flex flex-wrap border-b border-gray-400 dark:border-dark-900 w-full;
        @apply p-3 @sm:p-4 m-0;

        .field-inner {
            @apply w-full md:w-1/2 rtl:md:pl-8 ltr:md:pr-8;
        }

        .field-inner + div {
            @apply w-full md:w-1/2;
        }
    }
}
</style>
