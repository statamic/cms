<template>

    <div class="blueprint-builder">

        <header class="mb-3">
            <breadcrumb :url="breadcrumbUrl" :title="__('Blueprints')" />

            <div class="flex items-center justify-between">
                <h1>{{ initialTitle }}</h1>
                <button type="submit" class="btn-primary" @click.prevent="save" v-text="__('Save')" />
            </div>
        </header>

        <div class="publish-form card p-0">
            <div class="form-group">
                <label class="block">{{ __('Title') }}</label>
                <small class="help-block">{{ __('messages.blueprints_title_instructions') }}</small>
                <div v-if="errors.title">
                    <small class="help-block text-red" v-for="(error, i) in errors.title" :key="i" v-text="error" />
                </div>
                <input type="text" name="title" class="input-text" v-model="blueprint.title" autofocus="autofocus">
            </div>
        </div>

        <div class="content mt-5 mb-2">
            <h2>{{ __('Tab Sections') }}</h2>
            <p class="max-w-lg">{{ __('messages.tab_sections_instructions') }}</p>
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

    props: ['action', 'initialBlueprint', 'breadcrumbUrl'],

    data() {
        return {
            blueprint: clone(this.initialBlueprint),
            sections: [],
            initialTitle: this.initialBlueprint.title,
            errors: {}
        }
    },

    created() {
        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.save();
        });
    },

    watch: {

        sections(sections) {
            this.blueprint.sections = sections;
        },

        blueprint: {
            deep: true,
            handler() {
                this.$dirty.add('blueprints');
            }
        }

    },

    methods: {

        sectionsUpdated(sections) {
            this.sections = sections;
        },

        save() {
            // this.$axios[this.method](this.action, this.fieldset)
            this.$axios['patch'](this.action, this.blueprint)
                .then(response => this.saved(response))
                .catch(e => {
                    this.$toast.error(e.response.data.message);
                    this.errors = e.response.data.errors;
                })
        },

        saved(response) {
            this.$toast.success(__('Saved'));
            this.errors = {};
            this.$dirty.remove('blueprints');
        }

    }

}
</script>
