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

                <PublishContainer
                    v-if="hasFields && !resolving"
                    :name="containerName"
                    :blueprint="blueprint"
                    v-model="values"
                    :meta="meta"
                    :errors="errors"
                >
                    <FieldsProvider :fields="blueprint.tabs[0].fields">
                        <PublishFields />
                    </FieldsProvider>
                </PublishContainer>
            </div>
        </confirmation-modal>
    </div>
</template>

<script>
import uniqid from 'uniqid';
import { PublishContainer, FieldsProvider, PublishFields } from '@statamic/cms/ui';

export default {
    components: { PublishContainer, FieldsProvider, PublishFields },

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
        text: {
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
            blueprint: [],
            values: {},
            meta: {},
            error: null,
            errors: {},
            bodyText: null,
            containerName: `field-action-modal-${uniqid()}`,
        };
    },

    mounted() {
        this.bodyText = this.initializeBodyText();
        this.initialize();
    },

    computed: {
        hasFields() {
            return this.fields && Object.keys(this.fields).length > 0;
        },
    },

    methods: {
        initialize() {
            if (!this.hasFields) {
                return;
            }
            this.resolving = true;
            this.$axios
                .post(cp_url(`field-action-modal/resolve`), {
                    fields: this.fields,
                })
                .then((response) => {
                    this.blueprint = { tabs: [{ fields: response.data.fields }] };
                    this.values = response.data.values;
                    this.meta = response.data.meta;
                    this.resolving = false;
                });
        },

        confirm() {
            if (!this.hasFields) {
                this.$emit('confirm');
                return;
            }

            this.processing = true;
            this.$axios
                .post(cp_url('field-action-modal/process'), {
                    fields: this.fields,
                    values: this.values,
                })
                .then((response) => {
                    this.$emit('confirm', response.data);
                })
                .catch((e) => {
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
                })
                .finally(() => (this.processing = false));
        },

        cancel() {
            this.$emit('cancel');
        },

        initializeBodyText() {
            if (this.text) return this.text;

            if (this.warningText || this.hasFields) return null;

            return __('Are you sure?');
        },
    },
};
</script>
