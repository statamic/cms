<template>

    <div class="blueprint-builder">

        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ initialTitle }}</h1>
            <button type="submit" class="btn btn-primary" @click.prevent="save">Save</button>
        </div>

        <div class="publish-form card p-0 mb-3">

            <div class="form-group">
                <label class="block">{{ __('Title') }}</label>
                <small class="help-block">{{ __('The proper name of your blueprint.') }}</small>
                <div v-if="errors.title">
                    <small class="help-block text-red" v-for="(error, i) in errors.title" :key="i" v-text="error" />
                </div>
                <input type="text" name="title" class="input-text" v-model="blueprint.title" autofocus="autofocus">
            </div>

        </div>

        <sections
            :initial-sections="blueprint.sections"
            @updated="sectionsUpdated"
        />

    </div>

</template>

<script>
import uniqid from 'uniqid';
import Sections from './Sections.vue';

export default {

    components: {
        Sections,
    },

    props: ['action', 'initialBlueprint'],

    data() {
        return {
            blueprint: JSON.parse(JSON.stringify(this.initialBlueprint)),
            sections: [],
            initialTitle: this.initialBlueprint.title,
            errors: {}
        }
    },

    created() {
        this.addIds();
    },

    watch: {

        sections(sections) {
            this.blueprint.sections = sections;
        }

    },

    methods: {

        addIds() {
            this.sections = this.blueprint.sections.map(section => {
                section._id = uniqid();
                section.fields = section.fields.map(field => {
                    field._id = uniqid();
                    return field;
                });
                return section;
            });
        },

        sectionsUpdated(sections) {
            this.sections = sections;
        },

        save() {
            // this.$axios[this.method](this.action, this.fieldset)
            this.$axios['patch'](this.action, this.blueprint)
                .then(response => this.saved(response))
                .catch(e => {
                    this.$notify.error(e.response.data.message);
                    this.errors = e.response.data.errors;
                })
        },

        saved(response) {
            this.$notify.success('Saved');
            this.errors = {};
        }

    }

}
</script>
