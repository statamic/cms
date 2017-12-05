require('sweetalert');

module.exports = {

    template: require('./list.template.html'),

    props: {
        endpoint: String,
        type: {
            type: String,
            default: ''
        },
        no_results_heading: {
            type: String,
            default: 'This group has no items.'
        },
        no_results_subheading: {
            type: String,
            default: false
        },
        no_results_button: {
            type: String,
            default: 'New Item'
        },
        new_url: {
            type: String,
            default: ''
        },
        deleteMultiConfirmation:{
            type: String,
            default: 'You are about to delete multiple entries.'
        },
        mode: {
            type: String,
            default: "normal"
        }
    },

    components: {
        'field-status': require('./field-status'),
        'field-default': require('./field-default')
    },

    data: function () {
        return {
            loading: true,
            list: { rows: [], actions: {} },
            reordering: false,
            search: null
        }
    },

    computed: {
        hasActions: function() {
            return this.list.actions.length;
        },

        checkedEntries: function() {
            return this.list.rows.filter(function(entry) {
                return entry.checked;
            }).map(function(entry) {
                return entry.uuid;
            });
        },

        allEntriesChecked: function() {
            return this.list.rows.length === this.checkedEntries.length;
        }
    },

    methods: {
        deleteItem: function(item, index, endpoint, e) {
            e.preventDefault();
            self = this;

            swal({
                title: "Are you sure?",
                text: "You are about to delete this entry.",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes I'm sure.",
                closeOnConfirm: false
            }, function() {
                self.$http.delete(endpoint, {uuid: item.uuid}, function(data, status, request) {
                    swal(
                        "Deleted!",
                        "Your entry has been deleted.",
                        "success"
                    );
                    self.list.rows.$remove(index);
                });
            });
        },

        deleteMultiple: function() {
            var self = this;
            self.$event.preventDefault();

            swal({
                title: "Are you sure?",
                text: self.deleteMultiConfirmation,
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes I'm sure.",
                closeOnConfirm: false
            }, function() {
                self.$http.post(self.list.actions.delete.endpoint, {uuids: self.checkedEntries}, function(data, status, request) {
                    swal(
                        "Deleted!",
                        "Your entries have been deleted.",
                        "success"
                    );

                    // Remove checked items
                    self.list.rows = self.list.rows.filter(function(entry) {
                        return entry.checked === false;
                    });
                });
            });

        },

        checkAllEntries: function() {
            var status = ! this.allEntriesChecked;

            _.each(this.list.rows, function(entry) {
                entry.checked = status;
            });
        },

        reset: function() {
            this.search = '';
        },

        toggleReorder: function(e) {
            e.preventDefault();
            self = this;

            this.reordering = ! this.reordering;

            $(".sortable tbody").sortable({
                axis: "y",
                revert: 175,
                placeholder: "placeholder",
                forcePlaceholderSize: true,

                start: function(e, ui) {
                    ui.item.data('start', ui.item.index())
                },

                update: function(e, ui) {
                    var start = ui.item.data('start'),
                        end   = ui.item.index();

                    self.list.rows.splice(end, 0, self.list.rows.splice(start, 1)[0]);
                }

            }).disableSelection();

        },

        saveOrder: function(endpoint, e) {
            e.preventDefault();
            self = this;

            var order = $.map(this.list.rows, function(item, i) {
                return item.uuid;
            });

            this.$http.post(endpoint, {uuids: order}, function(data, status, request) {
                console.log('Entries Reordered');
                self.reordering = false;
            });

        },

        resolveComponent: function(key) {
            if ('field-' + key in this.$options.components) {
                return 'field-' + key;
            }

            return 'field-default';
        }
    },

    ready: function() {
        this.$http.get(this.endpoint, function(data, status, request) {
            this.list = data;
            this.loading = false;
        });
    }
};