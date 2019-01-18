<template>

    <div>
        <div class="flex items-center mb-3">
            <h1 class="flex-1">
                <small class="subhead block">
                    <a :href="collectionUrl" v-text="collectionTitle" class="text-grey hover:text-blue" />
                </small>
                {{ initialTitle }}
            </h1>
            <button
                class="btn btn-primary"
                :class="{ disabled: !canSave }"
                :disabled="!canSave"
                @click.prevent="save"
                v-text="__('Save')"
            ></button>
            <slot name="action-buttons-right" />
        </div>

        <publish-container
            v-if="fieldset"
            ref="container"
            :name="publishContainer"
            :fieldset="fieldset"
            :values="initialValues"
            :meta="initialMeta"
            :errors="errors"
            @updated="values = $event"
        >
            <div slot-scope="{ }">
                <publish-sections />
            </div>
        </publish-container>
    </div>

</template>


<script>
import axios from 'axios';
import Fieldset from '../publish/Fieldset';

export default {

    props: {
        publishContainer: String,
        initialFieldset: Object,
        initialValues: Object,
        initialMeta: Object,
        initialTitle: String,
        collectionTitle: String,
        collectionUrl: String,
        action: String,
        method: String
    },

    data() {
        return {
            saving: false,
            fieldset: null,
            values: _.clone(this.initialValues),
            meta: _.clone(this.initialMeta),
            error: null,
            errors: {}
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        },

        canSave() {
            return this.$progress.isComplete();
        }

    },

    created() {
        this.fieldset = new Fieldset(this.initialFieldset)
            .showSlug(true)
            .prependTitle()
            .prependMeta()
            .getFieldset();
    },

    watch: {

        saving(saving) {
            this.$progress.loading(`${this.publishContainer}-entry-publish-form`, saving);
        }

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.saving = true;
            this.clearErrors();

            axios[this.method](this.action, this.values).then(response => {
                this.saving = false;
                this.$notify.success('Saved');
                this.$refs.container.saved();
                this.$nextTick(() => this.$emit('saved', response));
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
            })
        }

    }

}
</script>
