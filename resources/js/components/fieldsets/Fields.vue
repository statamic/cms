<template>

    <div class="mt-3">

        <div v-if="fieldtypesLoading" class="card p-3 text-center">
            <loading-graphic  />
        </div>

        <template v-if="fieldtypesLoaded">

            <data-list v-show="fields.length" :rows="fields" :columns="columns" :sort="false">
                <div class="card p-0 mb-3" slot-scope="{}">
                    <data-list-table :reorderable="true" @reordered="$emit('updated', $event)">
                        <template slot="cell-display" slot-scope="{ row: field }">
                            <input
                                type="text"
                                class="bg-transparent outline-none w-full"
                                v-model="field.display"
                                :placeholder="field.handle"
                                @focus="$event.target.select()" />
                        </template>
                        <template slot="cell-handle" slot-scope="{ row: field }">
                            <input
                                type="text"
                                class="bg-transparent text-xs font-mono outline-none w-full"
                                v-model="field.handle"
                                @focus="$event.target.select()" />
                        </template>
                        <template slot="cell-type" slot-scope="{ value: type }">
                            <div class="flex">
                                <svg-icon :name="fieldtype(type).icon" class="w-4 h-4 mr-1" />
                                <span v-text="fieldtype(type).title" />
                            </div>
                        </template>
                        <template slot="cell-width" slot-scope="{ row: field }">
                            <width-selector v-model="field.width" />
                        </template>
                        <template slot="actions" slot-scope="{ row: field, index }">
                            <a class="mr-1 text-grey" @click.prevent="edit(field._id)"><span class="icon icon-pencil" /></a>
                            <a class="mr-1 text-grey" @click.prevent="destroy(index)"><span class="icon icon-cross" /></a>
                            <stack v-if="editingField === field._id" :name="`${field._id}-field-settings`" @closed="editingField = null">
                                <field-settings
                                    slot-scope="{ close }"
                                    ref="settings"
                                    :root="isRootLevel"
                                    :type="field.type"
                                    :config="field"
                                    @committed="fieldUpdated(field._id, $event)"
                                    @closed="close"
                                />
                            </stack>
                        </template>
                    </data-list-table>
                </div>
            </data-list>

            <button class="btn-default" @click="addField">+ {{ __('Add Field') }}</button>

            <stack v-if="selectingFieldtype" name="fieldtype-selector" @closed="selectingFieldtype = false">
                <fieldtype-selector slot-scope="{ close }" @closed="close" @selected="fieldtypeSelected" />
            </stack>

        </template>

    </div>

</template>

<script>
import uniqid from 'uniqid';
import FieldSettings from '../fields/Settings.vue';
import WidthSelector from '../fields/WidthSelector.vue';
import ProvidesFieldtypes from '../fields/ProvidesFieldtypes';
import FieldtypeSelector from '../fields/FieldtypeSelector.vue';

export default {

    components: {
        FieldSettings,
        FieldtypeSelector,
        WidthSelector,
     },

    mixins: [ProvidesFieldtypes],

    props: {
        initialFields: Array,
        isRootLevel: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            fields: null,
            editingField: null,
            selectingFieldtype: false,
            columns: [
                { label: __('Display'), field: 'display' },
                { label: __('Handle'), field: 'handle' },
                { label: __('Type'), field: 'type' },
                { label: __('Width'), field: 'width' },
            ]
        }
    },

    created() {
        this.fields = JSON.parse(JSON.stringify(this.initialFields))
            .map(field => Object.assign(field, { _id: uniqid() }));
    },

    watch: {

        fields: {
            deep: true,
            handler(fields) {
                this.$emit('updated', fields);
            }
        }

    },

    methods: {

        edit(id) {
            this.editingField = id;
        },

        destroy(index) {
            this.fields.splice(index, 1);
        },

        fieldtype(type) {
            return _.findWhere(this.fieldtypes, { handle: type });
        },

        addField() {
            this.selectingFieldtype = true;
            this.$nextTick(() => this.$modal.show('fieldtype-selector'));
        },

        fieldtypeSelected(field) {
            const id = uniqid();
            const handle = field.type;
            this.fields.push({
                ...field,
                _id: id,
                handle,
                display: handle.substring(0, 1).toUpperCase() + handle.substr(1),
            });
            this.selectingFieldtype = false;
            this.edit(id);
        },

        fieldUpdated(_id, field) {
            const i = _.indexOf(this.fields, _.findWhere(this.fields, { _id }));
            this.fields.splice(i, 1, field);
            this.editingField = null;
        }

    }

}
</script>
