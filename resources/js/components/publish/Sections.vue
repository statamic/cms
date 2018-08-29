<template>

    <div>

        <div class="publish-tabs tabs mb-2" v-show="mainSections.length > 1">
            <a href=""
                v-for="section in mainSections"
                :key="section.handle"
                :class="{
                    'active': section.handle == active,
                    'has-error': sectionHasError(section.handle)
                }"
                @click.prevent="active = section.handle"
                v-text="section.display || `${section.handle[0].toUpperCase()}${section.handle.slice(1)}`"
            ></a>
        </div>

        <div class="flex justify-between">
            <div class="w-full">
                <div
                    class="card p-0"
                    v-for="section in mainSections"
                    :key="section.handle"
                    v-show="section.handle === active"
                >
                    <publish-fields :fields="section.fields" />
                </div>
            </div>

            <div class="publish-sidebar ml-4" v-show="shouldShowSidebar">
                <div class="card p-0">
                    <publish-fields :fields="sidebarSection.fields" />
                </div>
            </div>
        </div>

    </div>

</template>

<script>
export default {

    inject: ['storeName'],

    data() {
        const state = this.$store.state.publish[this.storeName];

        return {
            active: state.fieldset.sections[0].handle
        }
    },

    computed: {

        state() {
            return this.$store.state.publish[this.storeName];
        },

        sections() {
            return this.state.fieldset.sections;
        },

        mainSections() {
            if (! this.shouldShowSidebar) return this.sections;

            return _.filter(this.sections, section => section.handle != 'sidebar');
        },

        sidebarSection() {
            return _.find(this.sections, { handle: 'sidebar' });
        },

        shouldShowSidebar() {
            const width = this.$store.state.statamic.windowWidth;

            // TODO: or is live previewing
            if (this.sidebarSection.fields.length == 0 || width < 1366) return false;

            return true;
        },

        errors() {
            return this.state.errors;
        },

        // A mapping of fields to which section they are in.
        sectionFields() {
            let fields = {};
            this.sections.forEach(section => {
                section.fields.forEach(field => {
                    fields[field.handle] = section.handle;
                })
            });
            return fields;
        },

        // A mapping of fields with errors to which section they are in.
        sectionErrors() {
            let errors = {};
            Object.keys(this.errors).forEach(field => {
                errors[field] = this.sectionFields[field];
            });
            return errors;
        },

    },

    methods: {


        sectionHasError(handle) {
            return _.chain(this.sectionErrors).values().contains(handle).value();
        }

    }

}
</script>
