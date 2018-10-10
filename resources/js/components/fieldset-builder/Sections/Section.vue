<template>
    <div class="section-layout">
        <div class="section-header p-3 border-b">
            <div>
                <label>{{ translate('cp.section_label') }}</label>
                <input type="text" class="section-display form-control" v-model="section.display" ref="display" />
            </div>
            <div>
                <label class="flex justify-between">
                    <span class="mr-sm">{{ translate('cp.section_handle') }}</span>
                    <i class="icon icon-info text-2xs opacity-25 hover:opacity-75" v-tip :tip-text="translate('cp.section_handle_tip')"></i>
                </label>
                <input type="text" class="section-handle form-control mono" v-model="section.handle" @keydown="handleModified = true" />
            </div>
        </div>

        <div class="px-3">
            <h2>Fields</h2>
        </div>

        <fieldset-fields
            v-ref=fields
            :fields.sync="section.fields"
            :section="section"
            :fieldtypes="fieldtypes"
            :is-adding="isAddingField"
            :is-quick-adding="isQuickAddingField"
            @selector-closed="fieldSelectorClosed"
            @updated="fieldsUpdated"
            classes="root-level-section-fields px-2"
        ></fieldset-fields>

        <div class="p-3 border-t">
            <button class="btn btn-primary mr-1" @click.prevent="addField">{{ translate('cp.add_field') }}</button>
            <button class="btn btn-default" @click.prevent="quickAddField">{{ translate('cp.quick_add') }}</button>
        </div>

    </div>

</template>


<script>
export default {

    props: [
        'fieldtypes',
        'section'
    ],

    data() {
        return {
            isAddingField: false,
            isQuickAddingField: false,
            handleModified: !RegExp(/^section_\d+/).test(this.section.handle)
        }
    },

    computed: {

        display() {
            return this.section.display;
        }

    },

    watch: {

        display(val) {
            if (!this.handleModified) {
                this.section.handle = this.$slugify(val, '_');
            }
        },

        handle(after, before) {
            if (before === after) return;

            if (before === 'sidebar') {
                this.$emit('no-longer-sidebar');
            }

            if (after === 'sidebar') {
                this.$emit('became-sidebar');
            }
        }

    },

    methods: {

        focus() {
            this.$refs.display.select();
        },

        fieldSelectorClosed() {
            this.isAddingField = false;
        },

        updateFieldWidths() {
            this.$refs.fields.updateFieldWidths();
        },

        addField() {
            this.isAddingField = true;
            this.isQuickAddingField = false;
        },

        quickAddField() {
            this.addField();
            this.isQuickAddingField = true;
        }

    }

}
</script>
