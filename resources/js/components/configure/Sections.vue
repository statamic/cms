<template>
    <element-container @resized="containerWidth = $event.width">
        <div>
            <div v-for="section in mainSections" :key="section.handle">
                <div class="mb-1 content">
                    <h2 v-text="section.display" class="text-base" />
                    <p v-html="section.instructions" />
                </div>
                <div class="card p-0 mb-5 configure-section">
                    <publish-fields
                        :fields="section.fields"
                        :read-only="readOnly"
                        :syncable="syncable"
                        @updated="(handle, value) => $emit('updated', handle, value)"
                        @meta-updated="(handle, value) => $emit('meta-updated', handle, value)"
                        @synced="$emit('synced', $event)"
                        @desynced="$emit('desynced', $event)"
                        @focus="$emit('focus', $event)"
                        @blur="$emit('blur', $event)"
                    />
                </div>
            </div>

            <portal-target :name="actionsPortal" class="publish-section publish-section-actions-footer" />
        </div>
    </element-container>
</template>

<script>
export default {

    inject: ['storeName'],

    props: {
        readOnly: Boolean,
        syncable: Boolean
    },

    data() {
        const state = this.$store.state.publish[this.storeName];

        return {
            active: state.blueprint.sections[0].handle,
            containerWidth: null
        }
    },

    computed: {

        state() {
            return this.$store.state.publish[this.storeName];
        },

        sections() {
            return this.state.blueprint.sections;
        },

        mainSections() {
            if (! this.shouldShowSidebar) return this.sections;

            if (this.active === "sidebar") {
                this.active = this.state.blueprint.sections[0].handle
            }

            return _.filter(this.sections, section => section.handle != 'sidebar');
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

        actionsPortal() {
            return `publish-actions-${this.storeName}`;
        }

    },

    methods: {

        sectionHasError(handle) {
            return _.chain(this.sectionErrors).values().contains(handle).value();
        }

    }

}
</script>
