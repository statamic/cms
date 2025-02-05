<template>
    <modal name="delete-entry-confirmation">
        <div class="confirmation-modal flex h-full flex-col">
            <div class="p-4 pb-0 text-lg font-medium">
                {{ __('Delete') }}
            </div>
            <div class="flex-1 px-4 py-6 text-gray dark:text-dark-150">
                <div class="publish-fields">
                    <div class="form-group" :class="{ 'has-error': this.error }">
                        <div class="field-inner">
                            <label class="publish-field-label" for="field_behavior">
                                <span v-text="__('Localizations')" />
                                <i class="required ltr:ml-1 rtl:mr-1">*</i>
                            </label>
                            <div class="help-block -mt-2"><p v-html="instructions" /></div>
                        </div>

                        <div class="button-group-fieldtype-wrapper">
                            <div class="btn-group">
                                <button
                                    @click="behavior = 'delete'"
                                    class="btn px-4"
                                    :class="{ active: behavior === 'delete' }"
                                >
                                    <span v-text="__('Delete')" />
                                </button>
                                <button
                                    @click="behavior = 'copy'"
                                    class="btn px-4"
                                    :class="{ active: behavior === 'copy' }"
                                >
                                    <span v-text="__('Detach')" />
                                </button>
                            </div>
                        </div>

                        <small
                            v-if="error"
                            class="help-block mb-0 mt-2 text-red-500"
                            v-text="__('statamic::validation.required')"
                        />
                    </div>
                </div>
            </div>
            <div
                class="flex items-center justify-end border-t bg-gray-200 p-4 text-sm dark:border-dark-900 dark:bg-dark-550"
            >
                <button
                    class="text-gray hover:text-gray-900 dark:text-dark-150 dark:hover:text-dark-100"
                    @click="$emit('cancel')"
                    v-text="__('Cancel')"
                />
                <button class="btn-danger ltr:ml-4 rtl:mr-4" @click="confirm" v-text="__('Confirm')" />
            </div>
        </div>
    </modal>
</template>

<script>
export default {
    props: {
        entries: { type: Number, required: true },
    },

    data() {
        return {
            behavior: null,
            error: false,
        };
    },

    computed: {
        instructions() {
            let url = docs_url('/tips/localizing-entries#deleting');

            return `${__('messages.choose_entry_localization_deletion_behavior')} <a href="${url}" target="_blank">${__('Learn more')}</a>`;
        },
    },

    methods: {
        confirm() {
            if (!this.behavior) {
                this.error = true;
                return;
            }

            this.$emit('confirm', this.behavior);
        },
    },
};
</script>
