<template>

    <div class="h-full overflow-auto p-8 bg-gray-300 h-full dark:bg-dark-800">

        <div class="flex items-center mb-6 -mt-2">
            <h1 class="flex-1">
                <small class="block text-xs text-gray-700 dark:text-dark-175 font-medium leading-none mt-2 flex items-center">
                    <svg-icon class="h-4 w-4 rtl:ml-2 ltr:mr-2 inline-block text-gray-700 dark:text-dark-175" name="paperclip"/>{{ __('Linked fieldset') }}
                </small>
                {{ __('Fieldset') }}
            </h1>
            <button
                class="text-gray-700 dark:text-dark-175 hover:text-gray-800 dark:hover:text-dark-100 rtl:ml-6 ltr:mr-6 text-sm"
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
                    :model-value="config.fieldset"
                    @update:model-value="updateField('fieldset', $event)"
                />

                <form-group
                    handle="prefix"
                    :display="__('Prefix')"
                    :instructions="__('messages.fieldset_import_prefix_instructions')"
                    :model-value="config.prefix"
                    @update:model-value="updateField('prefix', $event)"
                />

            </div>
        </div>
    </div>

</template>

<script>
export default {

    props: ['config'],

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
        }

    }

};
</script>
