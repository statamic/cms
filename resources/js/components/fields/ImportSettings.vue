<template>

    <div class="h-full overflow-auto p-8 bg-gray-300 h-full">

        <div class="flex items-center mb-6 -mt-2">
            <h1 class="flex-1">
                <small class="block text-xs text-gray-700 font-medium leading-none mt-2 flex items-center">
                    <svg-icon class="h-4 w-4 mr-2 inline-block text-gray-700" name="paperclip"/>{{ __('Linked fieldset') }}
                </small>
                {{ __('Fieldset') }}
            </h1>
            <button
                class="text-gray-700 hover:text-gray-800 mr-6 text-sm"
                @click.prevent="close"
                v-text="__('Cancel')"
            ></button>
            <button
                class="btn-primary"
                @click.prevent="commit"
                v-text="__('Finish')"
            ></button>
        </div>

        <div class="card">

            <div class="publish-fields @container">

                <form-group
                    handle="fieldset"
                    :display="__('Fieldset')"
                    :instructions="__('messages.fieldset_import_fieldset_instructions')"
                    autofocus
                    :value="config.fieldset"
                    @input="updateField('fieldset', $event)"
                />

                <form-group
                    handle="prefix"
                    :display="__('Prefix')"
                    :instructions="__('messages.fieldset_import_prefix_instructions')"
                    :value="config.prefix"
                    @input="updateField('prefix', $event)"
                />
            </div>
        </div>

        <div class="card p-0 mt-8">
            <div class="publish-fields @container">
                <field-conditions-builder
                    :config="config"
                    :suggestable-fields="suggestableConditionFields"
                    @updated="updateFieldConditions"
                    @updated-always-save="updateAlwaysSave" />
            </div>
        </div>
    </div>

</template>

<script>
    import {FIELD_CONDITIONS_KEYS, FieldConditionsBuilder} from "../field-conditions/FieldConditions";

export default {
    components: {FieldConditionsBuilder},

    props: {
        config: Object,
        suggestableConditionFields: Array,
    },

    model: {
        prop: 'config',
        event: 'input'
    },

    data: function() {
        return {
            values: clone(this.config),
        };
    },

    methods: {

        focus() {
            this.$els.display.select();
        },

        updateField(handle, value) {
            this.values[handle] = value;
        },

        commit() {
            this.$emit('committed', this.values);
            this.close();
        },

        close() {
            this.$emit('closed');
        },

        updateFieldConditions(conditions) {
            let values = {};

            _.each(this.values, (value, key) => {
                if (! FIELD_CONDITIONS_KEYS.includes(key)) {
                    values[key] = value;
                }
            });

            this.values = {...values, ...conditions};
        },

        updateAlwaysSave(alwaysSave) {
            this.values.always_save = alwaysSave;
        },
    }

};
</script>
