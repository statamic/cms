<template>

    <div class="h-full">

        <div v-if="loading" class="absolute pin z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <publish-container
            v-if="fieldset"
            name="relate-fieldtype-inline"
            :fieldset="fieldset"
            :values="initialValues"
            :meta="initialMeta"
            :errors="errors"
            @updated="values = $event"
        >
            <div class="editor-form h-full" slot-scope="{}">

                <div v-if="saving" class="saving flex justify-center text-center">
                    <loading-graphic text="Saving" />
                </div>

                <div class="editor-form-fields">
                    <div v-if="error" class="bg-red text-white p-2 shadow mb-2" v-text="error" />
                    <publish-fields :fields="fields" />
                </div>

                <div class="editor-form-actions">
                    <button type="button" class="btn btn-primary" :class="{ 'disabled': !canSave }" :disabled="!canSave" @click="save">
                        {{ __('Save') }}
                    </button>
                    <button class="text-xs ml-2 text-grey" @click="close">Cancel</button>
                </div>

            </div>
        </publish-container>

    </div>

</template>

<script>
import axios from 'axios';
import Fieldset from '../../publish/Fieldset';

export default {

    data() {
        return {
            action: null,
            loading: true,
            saving: false,
            fieldset: null,
            fields: null,
            values: null,
            initialValues: null,
            initialMeta: null,
            error: null,
            errors: {}
        }
    },

    computed: {

        canSave() {
            return this.$progress.isComplete();
        }

    },

    created() {
        this.getItem();
    },

    watch: {

        saving(saving) {
            this.$progress.loading('inline-publish-form', saving);
        }

    },

    methods: {

        getItem() {
            const url = cp_url('collections/blog/entries/create');

            axios.get(url).then(response => {
                const data = response.data;
                this.updateFieldset(data.blueprint);
                this.values = this.initialValues = data.values;
                this.initialMeta = data.meta;
                this.action = data.actions.store;
                this.loading = false;
            });
        },

        updateFieldset(blueprint) {
            const fieldset = new Fieldset(blueprint);
            this.fieldset = fieldset.fieldset;
            this.fields = _.chain(fieldset.sections)
                .map(section => section.fields)
                .flatten(true)
                .map(field => {
                    field.width = 100;
                    return field;
                }).value();
        },

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.saving = true;
            this.clearErrors();

            axios.post(this.action, this.values).then(response => {
                this.saving = false;
                this.$notify.success('Saved');
                this.$emit('created', response.data.entry);
                this.$nextTick(() => this.close());
            }).catch(e => {
                this.saving = false;
                if (e.response && e.response.status === 422) {
                    const { message, errors } = e.response.data;
                    this.error = message;
                    this.errors = errors;
                    this.$notify.error(message, { timeout: 2000 });
                } else {
                    this.$notify.error('Something went wrong');
                }
            });
        },

        close() {
            this.$emit('closed');
        }
    }

}
</script>
