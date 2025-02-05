<template>
    <div class="h-full overflow-auto bg-gray-300 p-8 dark:bg-dark-800">
        <div class="-mt-2 mb-6 flex items-center">
            <h1 class="flex-1">
                <small
                    class="mt-2 block flex items-center text-xs font-medium leading-none text-gray-700 dark:text-dark-175"
                >
                    <svg-icon
                        class="inline-block h-4 w-4 text-gray-700 dark:text-dark-175 ltr:mr-2 rtl:ml-2"
                        name="paperclip"
                    />{{ __('Linked fieldset') }}
                </small>
                {{ __('Fieldset') }}
            </h1>
            <button
                class="text-sm text-gray-700 hover:text-gray-800 dark:text-dark-175 dark:hover:text-dark-100 ltr:mr-6 rtl:ml-6"
                @click.prevent="close"
                v-text="__('Cancel')"
            ></button>
            <button class="btn-primary" @click.prevent="commit" v-text="__('Finish')"></button>
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
        event: 'input',
    },

    data: function () {
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
    },
};
</script>
