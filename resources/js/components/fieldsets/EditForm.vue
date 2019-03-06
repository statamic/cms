<template>

    <div>

        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ initialTitle }}</h1>
            <button type="submit" class="btn btn-primary" @click.prevent="save">Save</button>
        </div>

        <div class="publish-form card p-0">

            <div class="form-group">
                <label class="block">{{ __('Title') }}</label>
                <small class="help-block">{{ __('The proper name of your fieldset.') }}</small>
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
import Form from './Form.vue';

export default {

    mixins: [Form],

    data() {
        return {
            method: 'patch',
            initialTitle: this.initialFieldset.title
        }
    },

    methods: {

        saved(response) {
            this.$notify.success('Saved');
            this.errors = {};
        }

    }

}
</script>
