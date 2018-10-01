<template>

    <div class="card p-0 mt-3">

        <div v-if="fieldtypesLoading" class="p-3 text-center"><loading-graphic  /></div>

        <data-list :rows="fields" :columns="['display', 'handle', 'type']" :sort="false" v-if="fieldtypesLoaded">
            <div slot-scope="{}">
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
                    <template slot="actions" slot-scope="{ row: field }">
                        <a class="mr-1 text-grey" @click.prevent="edit(field._id)"><span class="icon icon-pencil" /></a>
                        <portal to="modals" v-if="editingField === field._id">
                            <modal :name="`${field._id}-field-settings`" width="90%" height="90%">
                                <field-settings ref="settings" :root="isRootLevel" v-model="field" />
                            </modal>
                        </portal>
                    </template>
                </data-table>
            </div>
        </data-list>


    </div>

</template>

<script>
import uniqid from 'uniqid';
import FieldSettings from '../fields/Settings.vue';
import ProvidesFieldtypes from '../fields/ProvidesFieldtypes';

export default {

    components: { FieldSettings },

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
            editingField: null
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

        fieldtype(type) {
            return _.findWhere(this.fieldtypes, { handle: type });
        }

    }

}
</script>
