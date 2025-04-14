<template>
    <div class="publish-sections">
        <div class="publish-sections-section" v-for="(section, i) in visibleSections" :key="i">
            <ui-card-panel
                :heading="__(section.display) || heading"
                :description="__(section.instructions) || description"
            >
                <publish-fields
                    :fields="section.fields"
                    :read-only="readOnly"
                    :syncable="syncable"
                    :syncable-fields="syncableFields"
                    @updated="(handle, value) => $emit('updated', handle, value)"
                    @meta-updated="(handle, value) => $emit('meta-updated', handle, value)"
                    @synced="$emit('synced', $event)"
                    @desynced="$emit('desynced', $event)"
                    @focus="$emit('focus', $event)"
                    @blur="$emit('blur', $event)"
                />
            </ui-card-panel>
        </div>
    </div>
</template>

<script>
import { ValidatesFieldConditions } from '../field-conditions/FieldConditions.js';

export default {
    emits: ['updated', 'meta-updated', 'synced', 'desynced', 'focus', 'blur'],

    mixins: [ValidatesFieldConditions],

    props: {
        sections: { type: Array, required: true },
        heading: { type: String, default: null },
        description: { type: String, default: null },
        readOnly: { type: Boolean, default: false },
        syncable: { type: Boolean, default: false },
        syncableFields: { type: Array, default: () => [] },
        namePrefix: { type: String, default: '' },
    },

    inject: ['publishContainer'],

    computed: {
        values() {
            return this.publishContainer.store.values;
        },

        extraValues() {
            return this.publishContainer.store.extraValues;
        },

        visibleSections() {
            return this.sections.filter((section) => this.sectionHasVisibleFields(section));
        },
    },

    methods: {
        sectionHasVisibleFields(section) {
            let visibleFields = 0;

            section.fields.forEach((field) => {
                if (this.showField(field)) visibleFields++;
            });

            return visibleFields > 0;
        },
    },
};
</script>
