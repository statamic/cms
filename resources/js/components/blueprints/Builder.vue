<template>

    <div class="blueprint-builder">

        <header class="mb-3">
            <div class="flex items-center justify-between">
                <h1 v-text="__('Edit Blueprint')" />
                <button type="submit" class="btn-primary" @click.prevent="save" v-text="__('Save')" />
            </div>
        </header>

        <div class="publish-form card p-0" v-if="showTitle">
            <div class="form-group">
                <label class="block">{{ __('Title') }}</label>
                <small class="help-block">{{ __('messages.blueprints_title_instructions') }}</small>
                <div v-if="errors.title">
                    <small class="help-block text-red" v-for="(error, i) in errors.title" :key="i" v-text="error" />
                </div>
                <input type="text" name="title" class="input-text" v-model="blueprint.title" autofocus="autofocus">
            </div>

            <div class="form-group">
                <label class="block">{{ __('Hidden') }}</label>
                <small class="help-block">{{ __('messages.blueprints_hidden_instructions') }}</small>
                <div v-if="errors.hidden">
                    <small class="help-block text-red" v-for="(error, i) in errors.hidden" :key="i" v-text="error" />
                </div>
                <toggle-input name="hidden" v-model="blueprint.hidden" />
            </div>
        </div>

        <div class="content mt-5 mb-2" v-if="useSections">
            <h2>{{ __('Tab Sections') }}</h2>
            <p class="max-w-lg">{{ __('messages.tab_sections_instructions') }}</p>
            <div v-if="errors.sections">
                <small class="help-block text-red" v-for="(error, i) in errors.sections" :key="i" v-text="error" />
            </div>
        </div>

        <sections
            :single-section="!useSections"
            :initial-sections="blueprint.sections"
            @updated="sectionsUpdated"
        />

    </div>

</template>

<script>
import Sections from './Sections.vue';

export default {

    components: {
        Sections,
    },

    props: {
        action: String,
        initialBlueprint: Object,
        showTitle: Boolean,
        useSections: { type: Boolean, default: true },
        isFormBlueprint: { type: Boolean, default: false },
    },

    data() {
        return {
            blueprint: this.initializeBlueprint(),
            sections: [],
            errors: {}
        }
    },

    created() {
        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.save();
        });

        if (this.isFormBlueprint) {
            Statamic.$config.set('isFormBlueprint', true);
        }
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

        initializeBlueprint() {
            let blueprint = clone(this.initialBlueprint);

            if (! this.showTitle) delete blueprint.title;

            return blueprint;
        },

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
