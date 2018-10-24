<template>

    <div class="flex flex-col h-full">

        <div class="flex items-center p-3 bg-grey-lightest border-b text-center">
            <svg-icon class="h-6 w-6 mr-2 inline-block opacity-50" name="template"></svg-icon>
            <span>Import</span>
        </div>

        <div class="flex-1 overflow-scroll">

            <div class="publish-fields">

                <form-group
                    handle="fieldset"
                    :display="__('Fieldset')"
                    :instructions="__(`The fieldset to be imported.`)"
                    autofocus
                    :value="config.fieldset"
                    @input="updateField('fieldset', $event)"
                />

                <form-group
                    handle="prefix"
                    :display="__('Prefix')"
                    :instructions="__(`The prefix that should be applied to each field when they are imported.`)"
                    :value="config.prefix"
                    @input="updateField('prefix', $event)"
                />

            </div>
        </div>
    </div>

</template>

<script>
import PublishField from '../publish/Field.vue';

export default {

    components: {
        PublishField,
    },

    props: ['config'],

    model: {
        prop: 'config',
        event: 'input'
    },

    data: function() {
        return {
            values: this.config,
        };
    },

    methods: {

        focus() {
            this.$els.display.select();
        },

        updateField(handle, value) {
            const values = this.values;
            values[handle] = value;
            this.$emit('input', values);
            this.$emit('updated', handle, value);
        }

    }

};
</script>
