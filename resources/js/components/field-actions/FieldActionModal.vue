<template>

    <div>
        <confirmation-modal
            :title="title"
            :danger="dangerous"
            :buttonText="buttonText"
            :busy="resolving || processing"
            @confirm="confirm"
            @cancel="cancel"
        >
            <div class="min-h-20">

                <div v-if="bodyText" v-text="bodyText" :class="{ 'mb-4': warningText || hasFields }" />

                <div v-if="warningText" v-text="warningText" class="text-red-500" :class="{ 'mb-4': hasFields }" />

                <publish-container
                    v-if="hasFields && !resolving"
                    name="confirm-action"
                    :blueprint="fieldset"
                    :values="values"
                    :meta="meta"
                    :errors="errors"
                    @updated="values = $event"
                >
                    <publish-fields
                        slot-scope="{ setFieldValue, setFieldMeta }"
                        :fields="fieldset.tabs[0].fields"
                        @updated="setFieldValue"
                        @meta-updated="setFieldMeta"
                    />
                </publish-container>

            </div>
        </confirmation-modal>
    </div>

</template>

<script>
export default {

    props: {
        fields: {
            type: Object,
        },
        title: {
            type: String,
        },
        buttonText: {
            type: String,
        },
        confirmationText: {
            type: String,
        },
        warningText: {
            type: String,
        },
        dangerous: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            resolving: this.hasFields,
            processing: false,
            fieldset: [],
            values: {},
            meta: {},
            error: null,
            errors: {},
            bodyText: (!this.confirmationText && this.warningText) ? null : (this.confirmationText ?? __('Are you sure?')),
        }
    },

    mounted() {
        this.initialize()
    },

    computed: {

        hasFields() {
            return this.fields && Object.keys(this.fields).length > 0;
        }

    },

    methods: {

        initialize() {
            if (!this.hasFields) {
                return;
            }
            this.resolving = true;
            this.$axios.post(cp_url(`field-action-modal/resolve`), {
                fields: this.fields,
            }).then(response => {
                this.fieldset = { tabs: [{ fields: response.data.fieldset }]};
                this.values = response.data.values;
                this.meta = response.data.meta;
                this.resolving = false;
            });
        },

        confirm() {
            if (!this.hasFields) {
                return;
            }
            this.processing = true;
            this.$axios.post(cp_url('field-action-modal/process'), {
                fields: this.fields,
                values: this.values,
            }).then(response => {
                this.$emit('confirm', response.data);
            }).catch(e => {
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$toast.error(message);
                } else if (e.response) {
                    this.$toast.error(e.response.data.message);
                } else {
                    this.$toast.error(__('Something went wrong'));
                }
            }).finally(() => this.processing = false);
        },

        cancel() {
            this.$emit('cancel')
        },

    },
}
</script>
