import Condition from './Condition.js';

module.exports = {
    data: function() {
        return { conditions: [] };
    },

    watch: {
        fieldData: {
            deep: true,
            handler: function(data) {
                this.runConditionals(data);
            },
        },

        fields: function() {
            this.watchFields();
        },
    },

    methods: {
        runConditionals: function(data) {
            this.conditions.forEach(condition => {
                condition.passes = condition.validate(data);
            });
        },

        peekaboo: function(field) {
            const condition = this.conditions.find(
                condition => condition.id === field.name
            );

            if (condition === undefined) {
                return true;
            }

            if (field.hide_when !== undefined) {
                return ! condition.passes;
            }

            return condition.passes;
        },

        watchFields: function() {
            this.conditions = this.fields
                .filter(field => field.show_when !== undefined || field.hide_when !== undefined)
                .map(field => new Condition(field.name, this.condition(field)));
        },

        condition: function(field) {
            if (field.show_when !== undefined) {
                return field.show_when;
            }

            return field.hide_when;
        },
    },
}
