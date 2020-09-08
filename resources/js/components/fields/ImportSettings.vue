<template>

    <div class="h-full overflow-auto p-4 bg-grey-30 h-full">

        <div class="flex items-center mb-3 -mt-1">
            <h1 class="flex-1">
                <small class="block text-xs text-grey-70 font-medium leading-none mt-1 flex items-center">
                    <svg-icon class="h-4 w-4 mr-1 inline-block text-grey-70" name="paperclip"/>{{ __('Linked fieldset') }}
                </small>
                {{ __('Fieldset') }}
            </h1>
            <button
                class="text-grey-70 hover:text-grey-80 mr-3 text-sm"
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

            <publish-fields-container>

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

            </publish-fields-container>
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
