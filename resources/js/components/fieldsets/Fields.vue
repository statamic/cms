<template>

    <div class="mt-3">

        <div v-if="fieldtypesLoading" class="card p-3 text-center">
            <loading-graphic  />
        </div>

        <template v-if="fieldtypesLoaded">

            <data-list :rows="fields" :columns="['display', 'handle', 'type']" :sort="false">
                <div class="card p-0 mb-3" slot-scope="{}">
                    <data-table>
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
                        <template slot="actions" slot-scope="{ row: field, index }">
                            <a class="mr-1 text-grey" @click.prevent="edit(field._id)"><span class="icon icon-pencil" /></a>
                            <a class="mr-1 text-grey" @click.prevent="destroy(index)"><span class="icon icon-cross" /></a>
                            <modal v-if="editingField === field._id" :name="`${field._id}-field-settings`" width="90%" height="90%">
                                <field-settings
                                    ref="settings"
                                    :root="isRootLevel"
                                    :type="field.type"
                                    v-model="field" />
                            </modal>
                        </template>
                    </data-table>
                </div>
            </data-list>

            <button class="btn btn-default" @click="addField">+ {{ __('Add Field') }}</button>

            <modal v-if="selectingFieldtype" name="fieldtype-selector" width="90%" height="90%" @closed="selectingFieldtype = false">
                <fieldtype-selector @selected="fieldtypeSelected" />
            </modal>

        </template>

    </div>

</template>

<script>
import uniqid from 'uniqid';
import FieldSettings from '../fields/Settings.vue';
import ProvidesFieldtypes from '../fields/ProvidesFieldtypes';
import FieldtypeSelector from '../fields/FieldtypeSelector.vue';

export default {

    components: {
        FieldSettings,
        FieldtypeSelector,
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
            this.$nextTick(() => this.$modal.show(`${id}-field-settings`));
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
            this.fields.push({
                ...field,
                _id: id,
                handle: 'new_field',
                display: 'New Field'
            });
            this.selectingFieldtype = false;
            this.edit(id);
        },

    }

}
</script>
