<template>

    <div>

        <div class="flexy mb-3">
            <h1 class="fill">{{ fieldset.title }}</h1>
            <button type="submit" class="btn btn-primary" @click.prevent="save">Save</button>
        </div>

        <div class="publish-form card p-0">

            <div class="form-group">
                <label class="block">{{ __('Title') }}</label>
                <small class="help-block">{{ __('The proper name of your fieldset.') }}</small>
                <input type="text" name="title" class="form-control" v-model="fieldset.title" autofocus="autofocus">
            </div>

        </div>

        <fieldset-fields
            :initial-fields="fieldset.fields"
            @updated="fieldsUpdated"
        />

    </div>

</template>

<script>
import axios from 'axios';
import FieldsetFields from './Fields.vue';

export default {

    components: {
        FieldsetFields
    },

    props: ['action', 'initialFieldset'],

    data() {
        return {
            fieldset: JSON.parse(JSON.stringify(this.initialFieldset))
        }
    },

    methods: {

        save() {
            axios.patch(this.action, this.fieldset).then(response => {
                this.$notify.success('Saved');
            }).catch(e => {
                this.$notify.error(e.response.data.message);
            })
        },

        fieldsUpdated(fields) {
            this.fieldset.fields = fields;
        }

    }

}
</script>
