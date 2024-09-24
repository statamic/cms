<template>

    <div>
        <confirmation-modal
            v-if="confirming"
            :title="action.title"
            :danger="action.dangerous"
            :buttonText="action.buttonText"
            :busy="resolving || processing"
            @confirm="confirm"
            @cancel="cancel"
        >
            <div v-if="action.confirmationText" v-text="action.confirmationText" :class="{ 'mb-4': warningText || showDirtyWarning || action.fields.length }" />

            <div v-if="action.warningText" v-text="action.warningText" class="text-red-500" :class="{ 'mb-4': showDirtyWarning || action.fields.length }" />

            <div v-if="showDirtyWarning" v-text="action.dirtyText" class="text-red-500" :class="{ 'mb-4': action.fields.length }" />

            <publish-container
                v-if="action.fields.length"
                name="confirm-action"
                :blueprint="fieldset"
                :values="values"
                :meta="action.meta"
                :errors="errors"
                @updated="values = $event"
            >
                <publish-fields
                    slot-scope="{ setFieldValue, setFieldMeta }"
                    :fields="action.fields"
                    @updated="setFieldValue"
                    @meta-updated="setFieldMeta"
                />
            </publish-container>
        </confirmation-modal>
    </div>

</template>

<script>
import Action from '../data-list/Action.vue';

export default {

    components: {
        Action,
    },

    props: {
        action: {
            type: Object,
            required: true,
        }
    },

    data() {
        return {
            resolving: true,
            processing: false,
            confirming: false,
            fieldset: {tabs:[{fields:this.action.fields}]},
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

        showDirtyWarning() {
            return this.isDirty && this.action.dirtyWarningText && ! this.action.bypassesDirtyWarning;
        },

    },

    methods: {

        initialize() {
            this.resolving = true;
            this.$axios.post(cp_url(`action-modal/resolve`), {
                fieldItems: this.fieldItems,
            }).then(response => {
                this.fields = response.data.fields;
                this.values = response.data.values;
                this.meta = response.data.meta;
                this.resolving = false;
            });
        },

        confirm() {
            this.processing = true;
            this.$axios.post(cp_url('action-modal/process'), {
                fieldItems: this.fieldItems,
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
