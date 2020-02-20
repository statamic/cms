<template>

    <div>

        <header class="mb-3">
            <breadcrumb :url="breadcrumbUrl" :title="__('Fieldsets')" />
            <div class="flex items-center justify-between">
                <h1>{{ initialTitle }}</h1>
                <button type="submit" class="btn-primary" @click.prevent="save" v-text="__('Save')" />
            </div>
        </header>

        <div class="publish-form card p-0">

            <div class="form-group">
                <label class="block">{{ __('Title') }}</label>
                <small class="help-block">{{ __('messages.fieldsets_title_instructions') }}</small>
                <div v-if="errors.title">
                    <small class="help-block text-red" v-for="(error, i) in errors.title" :key="i" v-text="error" />
                </div>
                <input type="text" name="title" class="input-text" v-model="fieldset.title" autofocus="autofocus">
            </div>

        </div>

        <fieldset-fields
            :initial-fields="fieldset.fields"
            @updated="fieldsUpdated"
        />

    </div>

</template>

<script>
import FieldsetFields from './Fields.vue';

export default {

    components: {
        FieldsetFields
    },

    props: ['action', 'initialFieldset', 'breadcrumbUrl'],

    data() {
        return {
            method: 'patch',
            initialTitle: this.initialFieldset.title,
            fieldset: JSON.parse(JSON.stringify(this.initialFieldset)),
            errors: {}
        }
    },

    methods: {

        save() {
            this.$axios[this.method](this.action, this.fieldset)
                .then(response => {
                    this.$toast.success(__('Saved'));
                    this.errors = {};
                })
                .catch(e => {
                    this.$toast.error(e.response.data.message);
                    this.errors = e.response.data.errors;
                })
        },

        fieldsUpdated(fields) {
            this.fieldset.fields = fields;
        },

    }

}
</script>
