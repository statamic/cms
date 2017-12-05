module.exports = {

    template: require('./fieldtype-selector.template.html'),

    props: ['fieldtypes', 'on-select'],

    data: function() {
        return {
            fieldtypeSelection: null,
            isActive: false,
        }
    },

    computed: {
        fieldtypeSelectionText: function() {
            return _.findWhere(this.fieldtypesSelectOptions, { value: this.fieldtypeSelection }).text;
        },

        fieldtypesSelectOptions: function() {
            var opts = this.fieldtypes.map(function(fieldtype) {
                return {text: fieldtype.label, value: fieldtype.name};
            });

            opts.unshift({ text: 'Select a field to add', value: null });

            // Disable title if there already is one
            var self = this;
            if (_.findWhere(self.$parent.fields, { name: 'title' })) {
                var title = _.indexOf(opts, _.findWhere(opts, { value: 'title' }));
                opts[title].disabled = true;
            }

            return opts;
        }
    },

    methods: {
        addField: function(fieldtype) {
            this.onSelect(this.fieldtypeSelection);
        }
    },

    ready: function() {
    }

};
