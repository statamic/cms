<template>

    <div>
        <table class="fields-table bordered-table">
            <thead>
                <tr>
                    <th>{{ __('Handle') }}</th>
                    <th>{{ __('Label') }}</th>
                    <th class="col-column">{{ __('Column') }}</th>
                    <th class="row-controls"></th>
                </tr>
            </thead>
            <tbody class="sortable" ref="tbody">
                <tr v-for="(field, $index) in fields">
                    <td class="col-handle"><input type="text" class="code" v-model="field.name" /></td>
                    <td class="col-display"><input type="text" v-model="field.display" /></td>
                    <td class="col-column">
                        <toggle-fieldtype :value="field.column" :config="{}" name="column"></toggle-fieldtype>
                    </td>
                    <td class="row-controls">
                        <span class="icon icon-edit edit" @click="selectField($index)"></span>
                        <span class="icon icon-menu move drag-handle"></span>
                        <span class="icon icon-cross delete" @click="deleteField($index)"></span>
                    </td>
                </tr>
            </tbody>
        </table>

        <portal to="modals" v-if="showEditModal">
            <modal name="field-settings" width="90%" height="90%" @closed="showEditModal = false">
                    <field-settings :field="fields[selectedField]">
                    </field-settings>
            </modal>
        </portal>

        <button type="button" class="btn btn-default" @click="addField">{{ __('Field') }} <i class="icon icon-plus icon-right"></i></button>
    </div>


</template>

<script>
export default {

    components: {
        'field-settings': require('./FieldSettings.vue')
    },

    props: {
        fields: {
            type: Array,
            default() { return [] }
        },
    },

    model: {
        prop: 'fields',
        event: 'input'
    },

    data: function() {
        return {
            showEditModal: false,
            selectedField: null
        }
    },

    methods: {

        selectField: function(index) {
            this.selectedField = index;
        },

        deselectField: function() {
            this.selectedField = null;
        },

        deleteField: function(index) {
            this.selectedField = null;
            this.fields.splice(index, 1);
        },

        addField: function() {
            var fieldsLength = this.fields.length || 0;
            var count = fieldsLength + 1;

            this.fields.push({
                name: 'field_' + count,
                display: 'Field ' + count,
                isNew: true
            });

            this.selectedField = count - 1;

            this.$nextTick(function () {
                $(this.$el).find('input').first().focus().select();
            });
        },

        enableSorting: function() {
            var self = this;

            $('.sortable').sortable({
                axis: 'y',
                revert: 175,
                placeholder: 'placeholder',
                handle: '.drag-handle',
                forcePlaceholderSize: true,

                start: function(e, ui) {
                    ui.item.data('start', ui.item.index());
                },

                update: function(e, ui) {
                    var start = ui.item.data('start'),
                        end   = ui.item.index();

                    self.fields.splice(end, 0, self.fields.splice(start, 1)[0]);
                }
            });
        }

    },

    watch: {
        selectedField: function (val) {
            this.showEditModal = (val !== null);
            this.$nextTick(() => this.$modal.show('field-settings'));
        },

        fields(fields) {
            this.$emit('input', fields);
        }
    },

    mounted() {
        this.enableSorting();
    }

}
</script>
