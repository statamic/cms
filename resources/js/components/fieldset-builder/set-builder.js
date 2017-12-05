module.exports = {

    template: require('./set-builder.template.html'),

    props: ['sets', 'fieldtypes'],

    data: function() {
        return {
            selectedSet: null,
            showEditModal: false
        }
    },

    methods: {

        fieldCount: function(set) {
            return _.keys(set.fields).length;
        },

        selectSet: function(index) {
            this.selectedSet = index;
        },

        deselectSet: function() {
            this.selectedSet = null;
        },

        deleteSet: function(index) {
            this.sets.splice(index, 1);
            this.selectedSet = null;
        },

        addSet: function() {
            var index = this.sets.length;
            var count = index + 1;

            this.sets.$set(index, {
                display: 'Set ' + count,
                name: 'set_' + count,
                instructions: '',
                fields: []
            });

            this.selectedSet = index;

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

                    self.sets.splice(end, 0, self.sets.splice(start, 1)[0]);
                }

            }).disableSelection();
        }

    },

    watch: {
        selectedSet: function (val) {
            this.showEditModal = (val !== null);
        },
        showEditModal: function (val) {
            if (! val) {
                this.deselectSet();
            } else {
                $(this.$el).find('.modal-body').find('input').first().focus().select();
            }
        }
    },

    ready: function() {
        this.enableSorting();
        this.sets = this.sets || [];
    }

};
