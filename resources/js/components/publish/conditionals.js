import Condition from './Condition.js';

export default {
    data: function() {
        return {
            conditions: [],
            hiddenFields: []
        };
    },

    methods: {
        evaluateConditions() {
            this.conditions.forEach(condition => {
                condition.passes = condition.validate(this.contentData);
            });

            this.hiddenFields = _.chain(this.fieldset.fields())
                .filter(field => !this.isVisible(field))
                .map(field => field.name)
                .value();
        },

        isVisible: function(field) {
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

        initConditions: function() {
            this.conditions = this.fieldset.fields()
                .filter(field => field.show_when !== undefined || field.hide_when !== undefined)
                .map(field => new Condition(field.name, this.condition(field)));

            this.evaluateConditions();

            this.$watch('contentData', data => this.evaluateConditions(data), { deep: true });
        },

        condition: function(field) {
            if (field.show_when !== undefined) {
                return field.show_when;
            }

            return field.hide_when;
        },
    },
}
