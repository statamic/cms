<template>

    <div class="blueprint-section" :class="{ 'w-full': isEditing }">
        <div class="blueprint-section-card card p-0 h-full flex flex-col">

            <div class="bg-grey-20 border-b text-sm flex rounded-t;">
                <div class="blueprint-drag-handle blueprint-section-drag-handle w-4 border-r"></div>
                <div class="p-1.5 py-1 flex-1">
                    <span class="font-medium mr-1">
                        <input ref="displayInput" type="text" v-model="section.display" class="bg-transparent w-full outline-none" />
                    </span>
                </div>
                <div class="flex items-center px-1.5">
                    <button @click.prevent="toggleEditing" class="text-grey-60 hover:text-grey-100 mr-1">
                        <svg-icon :name="isEditing ? 'shrink' : 'expand'" />
                    </button>
                    <button @click.prevent="$emit('deleted')" class="text-grey-60 hover:text-grey-100">
                        <svg-icon name="trash" />
                    </button>
                </div>
            </div>

            <div class="flex flex-col">

                <div class="blueprint-section-draggable-zone flex flex-wrap flex-1 mb-1 px-1 pt-2">
                    <component
                        v-for="(field, i) in section.fields"
                        :is="fieldComponent(field)"
                        :key="field._id"
                        :field="field"
                        :is-editing="editingField === field._id"
                        :is-section-expanded="isEditing"
                        :suggestable-condition-fields="suggestableConditionFields"
                        @edit="editingField = field._id"
                        @updated="fieldUpdated(i, $event)"
                        @deleted="deleteField(i)"
                        @editor-closed="editingField = null"
                    />
                </div>

                <div class="p-2 pt-0 flex items-center">
                    <button class="btn w-full flex justify-center items-center" @click="isCreating = true;">
                        <svg-icon name="wireframe" class="mr-1" />
                        {{ __('Create Field') }}
                    </button>
                    <link-fields @linked="fieldLinked" class="flex-1" />
                </div>

            </div>

            <stack name="fieldtype-selector"
                v-if="isCreating"
                @closed="isCreating = false"
            >
                <fieldtype-selector @selected="fieldtypeSelected" />
            </stack>

        </div>
    </div>

</template>

<script>
import uniqid from 'uniqid';
import LinkFields from './LinkFields.vue';
import RegularField from './RegularField.vue';
import ImportField from './ImportField.vue';
import FieldtypeSelector from '../fields/FieldtypeSelector.vue';

export default {

    components: {
        RegularField,
        ImportField,
        LinkFields,
        FieldtypeSelector,
    },

    props: {
        section: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            isEditing: false,
            isCreating: false,
            editingField: null
        }
    },

    computed: {
        suggestableConditionFields() {
            return this.section.fields.map(field => field.handle);
        }
    },

    watch: {

        section: {
            deep: true,
            handler(section) {
                this.$emit('updated', section);
            }
        },

        'section.display': function(display) {
            this.section.handle = this.$slugify(display, '_');
        }

    },

    methods: {

        fieldLinked(field) {
            this.section.fields.push(field);
            this.$notify.success(__('Field added.'));

            if (field.type === 'reference') {
                this.$nextTick(() => this.editingField = field._id);
            }
        },

        fieldUpdated(i, field) {
            this.section.fields.splice(i, 1, field);
        },

        deleteField(i) {
            this.section.fields.splice(i, 1);
        },

        fieldComponent(field) {
            return (field.type === 'import') ? 'ImportField' : 'RegularField';
        },

        focus() {
            this.$refs.displayInput.select();
        },

        toggleEditing() {
            this.isEditing = ! this.isEditing
        },

        fieldtypeSelected(field) {
            const id = uniqid();

            this.section.fields.push({
                _id: id,
                type: 'inline',
                handle: field.type,
                config: {
                    ...field,
                    display: field.type,
                }
            });

            this.$notify.success(__('Field added.'));
            this.isCreating = false;
            this.$nextTick(() => this.editingField = id);
        }

    }

}
</script>
