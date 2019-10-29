<template>

    <div>

        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ initialTitle }}</h1>
            <button type="submit" class="btn btn-primary" @click.prevent="save">Save</button>
        </div>

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

    props: ['action', 'initialFieldset'],

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
                    this.$toast.success('Saved');
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
