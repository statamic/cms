<template>

    <div class="blueprint-section">
        <div class="blueprint-section-card card p-0 h-full flex flex-col">

            <div class="bg-grey-lightest border-b text-sm flex rounded-t;">
                <div class="blueprint-drag-handle blueprint-section-drag-handle w-4 border-r"></div>
                <div class="px-2 py-1 flex-1">
                    <span class="font-medium mr-1">
                        <input type="text" v-model="section.display" class="bg-transparent w-full outline-none" />
                    </span>
                </div>
                <div class="flex items-center px-1">
                    <button class="opacity-50 hover:opacity-100 mr-1"><span class="icon icon-cog" /></button>
                    <button @click.prevent="$emit('deleted')" class="opacity-50 hover:opacity-100"><span class="icon icon-cross" /></button>
                </div>
            </div>

            <div class="p-2 flex flex-col flex-1">

                <div class="blueprint-section-draggable-zone flex-1 mb-2">
                    <component
                        v-for="(field, i) in section.fields"
                        :is="fieldComponent(field)"
                        :key="field._id"
                        :field="field"
                        :is-editing="editingField === field._id"
                        @edit="editingField = field._id"
                        @editor-closed="editingField = null"
                        @updated="fieldUpdated(i, $event)"
                        @deleted="deleteField(i)"
                    />
                </div>

                <div>
                    <add-field @added="fieldAdded" />
                </div>

            </div>

        </div>
    </div>

</template>

<script>
import AddField from './AddField.vue';
import RegularField from './RegularField.vue';
import ImportField from './ImportField.vue';

export default {

    components: {
        RegularField,
        ImportField,
        AddField,
    },

    props: {
        section: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            isAddingField: true,
            editingField: null
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

        fieldAdded(field) {
            this.section.fields.push(field);
            this.$nextTick(() => this.editingField = field._id);
        },

        fieldUpdated(i, field) {
            this.section.fields.splice(i, 1, field);
        },

        deleteField(i) {
            this.section.fields.splice(i, 1);
        },

        fieldComponent(field) {
            return (field.type === 'import') ? 'ImportField' : 'RegularField';
        }

    }

}
</script>
