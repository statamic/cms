<template>
    <PublishContainer
        name="dictionary-fields"
        :blueprint="blueprint"
        :model-value="value"
        :meta="publishMeta"
        :errors="errors"
        as-config
        @update:model-value="update"
    >
        <FieldsProvider :fields="fields">
            <Fields />
        </FieldsProvider>
    </PublishContainer>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { PublishContainer, FieldsProvider, PublishFields as Fields } from '@statamic/cms/ui';

export default {
    mixins: [Fieldtype],

    components: {
        PublishContainer,
        FieldsProvider,
        Fields,
    },

    computed: {
        dictionary() {
            return this.value?.type;
        },

        fields() {
            return this.meta.type.fields.concat(this.meta.dictionaries[this.dictionary]?.fields || []);
        },

        blueprint() {
            return {
                tabs: [
                    {
                        fields: this.fields,
                    },
                ],
            };
        },

        publishMeta() {
            return {
                ...this.meta.type.meta,
                ...this.meta.dictionaries[this.dictionary]?.meta,
            };
        },

        errors() {
            const state = this.publishContainer;

            if (!state) {
                return {};
            }

            let errors = {};

            // Filter errors to only include those for this field, and remove the field path prefix
            // if there is one, then append it to the errors object.
            Object.entries(state.errors)
                .filter(([key, value]) => key.startsWith(this.fieldPathPrefix || this.handle))
                .forEach(([key, value]) => {
                    errors[key.split('.').pop()] = value;
                });

            return errors;
        },
    },

    watch: {
        dictionary(dictionary) {
            this.update({
                type: dictionary,
                ...this.meta.dictionaries[dictionary]?.defaults,
            });
        },
    },
};
</script>
