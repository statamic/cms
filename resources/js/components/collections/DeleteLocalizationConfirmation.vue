<template>

    <modal name="delete-entry-confirmation" :pivotY="0.1" :overflow="false">
        <div class="confirmation-modal flex flex-col h-full">
            <div class="text-lg font-medium p-2 pb-0">
                {{ __('Delete') }}
            </div>
            <div class="flex-1 px-2 py-3 text-grey">
                <div class="publish-fields">
                    <div class="form-group" :class="{ 'has-error': this.error }">
                        <div class="field-inner">
                            <label class="publish-field-label" for="field_behavior">
                                <span v-text="__('Localizations')" />
                                <i class="required ml-sm">*</i>
                            </label>
                            <div class="help-block -mt-1"><p v-html="instructions" /></div>
                        </div>

                        <div class="button-group-fieldtype-wrapper">
                            <div class="btn-group">
                                <button @click="behavior = 'delete'" class="btn px-2" :class="{ active: behavior === 'delete' }"><span v-text="__('Delete')" /></button>
                                <button @click="behavior = 'copy'" class="btn px-2" :class="{ active: behavior === 'copy' }"><span v-text="__('Detach')" /></button>
                            </div>
                        </div>

                        <small v-if="error" class="help-block text-red mt-1 mb-0" v-text="__('statamic::validation.required')" />
                    </div>
                </div>
            </div>
            <div class="p-2 bg-grey-20 border-t flex items-center justify-end text-sm">
                <button class="text-grey hover:text-grey-90"
                    @click="$emit('cancel')"
                    v-text="__('Cancel')" />
                <button class="btn ml-2 btn-danger"
                    @click="confirm"
                    v-text="__('Confirm')" />
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
        }
    },

    computed: {
        instructions() {
            let url = docs_url('/tips/localizing-entries#deleting');

            return `${__('messages.choose_entry_localization_deletion_behavior')} <a href="${url}" target="_blank">${__('Learn more')}</a>`;
        }
    },

    methods: {

        confirm() {
            if (! this.behavior) {
                this.error = true;
                return;
            }

            this.$emit('confirm', this.behavior);
        }

    }

}
</script>
