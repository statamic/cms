module.exports = {

    template: require('./fields-builder.template.html'),

    props: [
        'data',
        'config',
        'name',
        'fields',
        'fieldtypes',
        'root'
    ],

    data: function() {
        return {
            selectedField: null
        }
    },

    computed: {

        hasFields: function() {
            return this.fields && this.fields.length;
        },

        selectedFieldConfig: function() {
            if (this.selectedField === null) {
                return [];
            }

            var type = this.fields[this.selectedField].type;
            var fieldtype = _.findWhere(this.fieldtypes, { name: type });
            return fieldtype.config;
        }

    },

    methods: {

        addField: function(fieldtype) {
            var fieldsLength = this.fields.length || 0;
            var count = fieldsLength + 1;

            var tmp = _.findWhere(this.fieldtypes, { name: fieldtype });
            var field = $.extend({}, tmp);

            field.type = field.name;
            field.name = 'field_' + count;
            field.display = 'Field ' + count;
            field.instructions = null;
            field.isNew = true;
            delete field.config;
            delete field.label;
            delete field.canBeValidated;
            delete field.canBeLocalized;
            delete field.canHaveDefault;

            if (field.type === 'title') {
                // Title field goes first with predefined values
                field.display = translate('cp.title');
                field.name = 'title';
                this.fields.unshift(field);
                this.selectedField = 0;
            } else {
                // Other fields go to the end.
                this.fields.push(field);
                this.selectedField = count - 1;
            }
        },

        selectField: function(index) {
            this.selectedField = index;
            this.tab = 'edit';
        },

        deleteField: function(index) {
            this.selectedField = null;
            this.fields.splice(index, 1);
        },

        sortFields: function(from, to) {
            this.fields.splice(to, 0, this.fields.splice(from, 1)[0]);
            this.ensureTitleIsFirst();
        },
        
        ensureTitleIsFirst: function() {
            var self = this;
            var title = _.indexOf(self.fields, _.findWhere(self.fields, { name: 'title' }));
            
            // If there's no title, do nothing.
            if (title === -1) {
                return;
            }
            
            // If title isn't first, make it so.
            if (this.fields[0].name !== 'title') {
                this.sortFields(title, 0);
            }
        }

    },

    ready: function() {
        this.fields = this.fields || [];
        this.root = Boolean(this.root || false);
        this.ensureTitleIsFirst();
    }

};
