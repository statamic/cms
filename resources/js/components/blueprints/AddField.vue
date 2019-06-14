<template>

    <div>
        <popper ref="popper" trigger="click" :append-to-body="true" boundaries-selector="body" :options="{ placement: 'right' }">
            <div class="popover w-96">
                <div class="popover-inner p-2">
                    <div>
                        <p class="text-sm font-medium mb-1">Select a field from an existing fieldset:</p>
                        <v-select
                            @input="addReferenceField"
                            name="field"
                            :placeholder="__('Existing fields')"
                            :options="suggestions"
                            :multiple="false"
                            :searchable="true"
                            :value="fieldReference">
                            <template slot="option" slot-scope="option">
                                <div class="flex items-center">
                                    <span v-text="option.fieldset" class="text-2xs text-grey-50 mr-1" />
                                    <span v-text="option.label" />
                                </div>
                            </template>
                        </v-select>

                    </div>

                    <div class="border-grey-30 border-t mt-2 pt-2 text-grey-40 text-xs">
                        <div class="mb-1">More options:</div>
                        <ul class="pl-2">
                            <li><button class="text-blue" @click="addInlineField">Create a one-time field</button></li>
                            <li><button class="text-blue" @click.prevent="createFieldsetField">Create a new reusable field</button></li>
                            <li><button class="text-blue" @click="addImportField">Import a fieldset</button></li>
                        </ul>
                    </div>

                    <stack name="fieldtype-selector" v-if="selectingFieldtype" @closed="selectingFieldtype = false">
                        <fieldtype-selector @selected="fieldtypeSelected" />
                    </stack>

                    <stack name="fieldset-field-form" v-if="creatingFieldsetField" @closed="creatingFieldsetField = false">
                        <fieldset-field-form @created="fieldsetFieldCreated" />
                    </stack>

                </div>
            </div>
            <button slot="reference" class="btn w-full flex justify-center items-center">
                <svg-icon name="wireframe" class="mr-1" />
                {{ __('Create Field') }}
            </button>
        </popper>

    </div>

</template>

<script>
import uniqid from 'uniqid';
import Popper from 'vue-popperjs';
import FieldtypeSelector from '../fields/FieldtypeSelector.vue';
import FieldsetFieldForm from './FieldsetFieldForm.vue';

export default {

    components: {
        Popper,
        FieldtypeSelector,
        FieldsetFieldForm
    },

    data() {
        return {
            fieldReference: null,
            selectingFieldtype: false,
            creatingFieldsetField: false,
            suggestions: Object.values(window.Statamic.$config.get('fieldsetFields')).map(field => {
                return {
                    value: `${field.fieldset.handle}.${field.handle}`,
                    label: field.display,
                    fieldset: field.fieldset.title
                };
            })
        }
    },

    computed: {

        isAddingReferenceField() {
            return this.type === 'reference';
        }

    },

    methods: {

        addReferenceField(reference) {
            if (!reference) return;

            this.fieldReference = reference;

            const field = JSON.parse(JSON.stringify(window.Statamic.$config.get('fieldsetFields')[reference.value]));

            this.$emit('added', {
                _id: uniqid(),
                type: 'reference',
                field_reference: reference,
                handle: field.handle,
                config: field.config,
                config_overrides: []
            });

            this.fieldReference = null;
            this.$refs.popper.doClose();
        },

        addInlineField() {
            this.selectingFieldtype = true;
            this.$refs.popper.doClose();
        },

        fieldtypeSelected(field) {
            this.$emit('added', {
                _id: uniqid(),
                type: 'inline',
                handle: field.type,
                config: {
                    ...field,
                    display: field.type,
                },
            });
        },

        addImportField() {
            this.$refs.popper.doClose();
            this.$emit('added', {
                _id: uniqid(),
                type: 'import',
                fieldset: null,
                prefix: null,
            });
        },

        createFieldsetField() {
            this.creatingFieldsetField = true;
            this.$refs.popper.doClose();
            this.$nextTick(() => this.$modal.show('fieldset-field-form'));
        },

        fieldsetFieldCreated(reference) {
            this.$modal.hide('fieldset-field-form');
            this.creatingFieldsetField = false;
            this.addReferenceField(reference);
        }

    }

}
</script>
