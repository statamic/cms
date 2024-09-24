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

            <div v-if="confirmationText" v-text="confirmationText" :class="{ 'mb-4': warningText || showDirtyWarning || fields.length }" />

            <div v-if="warningText" v-text="warningText" class="text-red-500" :class="{ 'mb-4': showDirtyWarning || fields.length }" />

            <div v-if="showDirtyWarning" v-text="dirtyText" class="text-red-500" :class="{ 'mb-4': fields.length }" />

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
        dirtyText: {
            type: String,
        },
        dirtyWarningText: {
            type: String,
        },
        dangerous: {
            type: Boolean,
            default: false,
        },
        bypassesDirtyWarning: {
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
            isDirty: false,
        }
    },

    mounted() {
        this.initialize()
    },

    computed: {

        hasFields() {
            return Object.keys(this.fields).length > 0;
        },

        showDirtyWarning() {
            return this.isDirty && this.dirtyWarningText && ! this.bypassesDirtyWarning;
        },

    },

    methods: {

        initialize() {
            if (!this.hasFields) {
                return;
            }
            this.resolving = true;
            this.$axios.post(cp_url(`action-modal/resolve`), {
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
            this.$axios.post(cp_url('action-modal/process'), {
                fields: this.fields,
                values: this.values,
            }).then(response => {
                this.processing = false;
                this.$emit('confirm', response.data.values);
            }).catch(e => {
                this.processing = false;
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
            });
        },

        cancel() {
            this.$emit('cancel')
        },

    },
}
</script>
