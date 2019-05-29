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

        <div class="flex justify-between">
            <div
                class="publish-section"
                :class="{ 'rounded-tl-none' : mainSections.length > 1 }"
                v-for="section in mainSections"
                :key="section.handle"
                v-show="section.handle === active"
            >
                <publish-fields
                    :fields="section.fields"
                    :read-only="readOnly"
                    :syncable="syncable"
                    @updated="(handle, value) => $emit('updated', handle, value)"
                    @synced="$emit('synced', $event)"
                    @desynced="$emit('desynced', $event)"
                    @focus="$emit('focus', $event)"
                    @blur="$emit('blur', $event)"
                />
            </div>

            <div :class="{ 'publish-sidebar': shouldShowSidebar }">
                <div class="publish-section">
                    <div class="publish-section-actions" :class="{ 'as-sidebar': shouldShowSidebar }">
                        <portal :to="actionsPortal" :disabled="shouldShowSidebar">
                            <slot name="actions" :should-show-sidebar="shouldShowSidebar" />
                        </portal>
                    </div>

                    <publish-fields
                        v-if="shouldShowSidebar && sidebarSection"
                        :fields="sidebarSection.fields"
                        :read-only="readOnly"
                        :syncable="syncable"
                        @updated="(handle, value) => $emit('updated', handle, value)"
                        @synced="$emit('synced', $event)"
                        @desynced="$emit('desynced', $event)"
                        @focus="$emit('focus', $event)"
                        @blur="$emit('blur', $event)"
                    />
                </div>
            </div>
        </div>

        <portal-target :name="actionsPortal" />

    </div>
    </element-container>

</template>

<script>
export default {

    inject: ['storeName'],

    props: {
        readOnly: Boolean,
        syncable: Boolean,
    },

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
            return this.containerWidth > 1010;
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
