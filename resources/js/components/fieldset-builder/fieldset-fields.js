module.exports = {

    template: require('./fieldset-fields.template.html'),

    props: ['fields', 'fieldtypes', 'onSelect', 'selectedField', 'onSort', 'onDelete', 'fieldtypeConfig', 'root'],

    data: function() {
        return {
            showEditModal: false
        };
    },

    computed: {
        canLocalize: function() {
            return this.root && Object.keys(Statamic.locales).length > 1;
        }
    },

    methods: {
        selectField: function(index) {
            this.selectedField = index;
            this.onSelect(index);
        },

        deselectField: function() {
            this.selectedField = null;
            this.onSelect(null);
        },

        deleteField: function(index) {
            this.onDelete(index);
        },

        fieldtypeLabel: function(type) {
            return _.findWhere(this.fieldtypes, {name: type}).label;
        },

        enableSorting: function() {
            var self = this;

            $(this.$els.tbody).sortable({
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

                    self.onSort(start, end);
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
        this.root = Boolean(this.root || false);

        this.enableSorting();
    }

};
