module.exports = {

    template: require('./formset-fields.template.html'),

    components: {
        'field-settings': require('./field-settings')
    },

    props: {
        fields: Array
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
        },
        showEditModal: function (val) {
            if (! val) {
                this.deselectField();
            } else {
                $(this.$el).find('.modal-body').find('input').first().focus().select();
            }
        }
    },

    ready: function() {
        this.enableSorting();
    }

};
