<template>

    <div class="blueprint-builder">

        <div class="flexy mb-3">
            <h1 class="fill">{{ initialTitle }}</h1>
            <button type="submit" class="btn btn-primary" @click.prevent="save">Save</button>
        </div>

        <div class="publish-form card p-0 mb-3">

            <div class="form-group">
                <label class="block">{{ __('Title') }}</label>
                <small class="help-block">{{ __('The proper name of your blueprint.') }}</small>
                <div v-if="errors.title">
                    <small class="help-block text-red" v-for="(error, i) in errors.title" :key="i" v-text="error" />
                </div>
                <input type="text" name="title" class="form-control" v-model="blueprint.title" autofocus="autofocus">
            </div>

        </div>

        <div class="text-center little-heading mb-3 opacity-50">Sections &amp; Fields</div>

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
        }

    }

}
</script>
