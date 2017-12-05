<template>
    <div class="array-fieldtype-container">

        <div class='grid-field array-keyed' v-if="componentType === 'keyed'">
            <table class="grid-table grid-mode-table headless">
                <tbody>
                    <tr v-if="data" v-for="key in config.keys">
                        <th>{{ key.text }}</th>
                        <td>
                            <input type="text" class="form-control" v-model="data[key.value]" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <template v-if="componentType === 'dynamic'">
            <div class="grid-field array-dynamic">
                <table class="grid-table grid-mode-table" v-if="hasRows">
                    <thead>
                        <tr>
                            <th>{{ valueHeader }}</th>
                            <th>{{ textHeader }}</th>
                            <th class="row-controls"></th>
                        </tr>
                    </thead>
                    <tbody v-el:tbody>
                        <tr v-for="(rowIndex, row) in data">
                            <td>
                                <input type="text" class="form-control" v-model="row.value" />
                            </td>
                            <td>
                                <input type="text" class="form-control" v-model="row.text" />
                            </td>
                            <td class="row-controls">
                                <span class="icon icon-menu move drag-handle"></span>
                                <span class="icon icon-cross delete" v-on:click="deleteRow(rowIndex)"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <button type="button" class="btn btn-default" @click="addRow">
                    {{ addRowButton }} <i class="icon icon-plus"></i>
                </button>
            </div>
        </template>

    </div>

</template>

<script>
module.exports = {

    mixins: [Fieldtype],

    ready: function() {
        this.data = this.data || [];

        if (this.componentType === 'keyed') {
            this.data = (this.data.length === 0) ? {} : this.data;
        }

        if (this.componentType === 'dynamic') {
            this.initSortable();
        }
    },

    computed: {
        componentType: function() {
            return (this.config.keys) ? 'keyed' : 'dynamic';
        },

        hasRows: function() {
            return this.data && this.data.length > 0;
        },

        addRowButton: function() {
            return this.config.add_row || translate_choice('cp.add_row', 1);
        },

        valueHeader: function() {
            return this.config.value_header || 'Value';
        },

        textHeader: function() {
            return this.config.text_header || 'Text';
        }
    },

    methods: {
        addRow: function() {
            this.data.push({ value: '', text: '' });
        },

        deleteRow: function(index) {
            this.data.splice(index, 1);
        },

        initSortable: function() {
            var self = this;
            var start = '';

            $(this.$els.tbody).sortable({
                axis: "y",
                revert: 175,
                handle: '.drag-handle',
                placeholder: 'table-row-placeholder',
                forcePlaceholderSize: true,

                start: function(e, ui) {
                    start = ui.item.index();
                    ui.placeholder.height(ui.item.height());
                },

                update: function(e, ui) {
                    var end  = ui.item.index(),
                        swap = self.data.splice(start, 1)[0];

                    self.data.splice(end, 0, swap);
                }
            });
        }
    }

};

</script>
