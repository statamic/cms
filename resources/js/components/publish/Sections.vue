<template>

    <element-container @resized="containerWidth = $event.width">
    <div>

        <div class="publish-tabs tabs" v-show="mainSections.length > 1">
            <a v-for="section in mainSections"
                :key="section.handle"
                :class="{
                    'active': section.handle == active,
                    'has-error': sectionHasError(section.handle)
                }"
                @click.prevent="active = section.handle"
                v-text="section.display || `${section.handle[0].toUpperCase()}${section.handle.slice(1)}`"
            ></a>
        </div>

        <div :class="{ 'flex justify-between': !livePreview }">
            <div class="w-full">
                <div
                    :class="{ 'card p-0': !livePreview, 'rounded-tl-none' : mainSections.length > 1 }"
                    v-for="section in mainSections"
                    :key="section.handle"
                    v-show="section.handle === active"
                >
                    <publish-fields :fields="section.fields" :live-preview="livePreview" />
                </div>
            </div>

            <div class="publish-sidebar" :class="{ 'ml-4': !livePreview }" v-if="shouldShowSidebar">
                <div :class="{ 'card p-0': !livePreview }">
                    <publish-fields :fields="sidebarSection.fields" :live-preview="livePreview" />
                </div>
            </div>
        </div>

    </div>
    </element-container>

</template>

<script>
export default {

    props: {
        livePreview: Boolean
    },

    inject: ['storeName'],

    data() {
        const state = this.$store.state.publish[this.storeName];

        return {
            active: state.fieldset.sections[0].handle,
            containerWidth: null
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
            if (! this.sidebarSection) return false;

            if (this.livePreview) return false;

            if (this.sidebarSection.fields.length == 0 || this.containerWidth < 1000) return false;

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
        }

    },

    methods: {


        sectionHasError(handle) {
            return _.chain(this.sectionErrors).values().contains(handle).value();
        }

    }

}
</script>
