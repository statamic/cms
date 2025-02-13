<template>
    <div class="publish-sections">
        <div class="publish-sections-section" v-for="(section, i) in visibleSections" :key="i">
            <div class="card p-0">
                <header class="publish-section-header @container" v-if="section.display">
                    <div class="publish-section-header-inner">
                        <label v-text="__(section.display)" class="text-base font-semibold" />
                        <div class="help-block" v-if="section.instructions">
                            <p v-html="$markdown(__(section.instructions))" />
                        </div>
                    </div>
                </header>
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
            </div>
        </div>
    </div>
</template>

<script>
import { ValidatesFieldConditions } from '../field-conditions/FieldConditions.js';

export default {
    emits: ['updated', 'meta-updated', 'synced', 'desynced', 'focus', 'blur'],

    mixins: [ValidatesFieldConditions],

    props: {
        sections: {
            type: Array,
            required: true,
        },
        readOnly: Boolean,
        syncable: Boolean,
        syncableFields: Array,
        namePrefix: String,
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
