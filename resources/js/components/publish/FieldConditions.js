const OPERATORS = ['==', '!=', '===', '!==', '>', '>=', '<', '<='];

export default {
    methods: {
        showField(field) {
            if (field.if === undefined) {
                return true;
            }

            let failedConditions = _.chain(field.if)
                .map((condition, field) => this.normalizeCondition(field, condition))
                .reject(condition => this.passesCondition(condition))
                .value();

            return _.isEmpty(failedConditions);
        },

        normalizeCondition(field, condition) {
            return {
                'lhs': this.normalizeConditionLhs(field),
                'operator': this.normalizeConditionOperator(condition),
                'rhs': this.normalizeConditionRhs(condition)
            };
        },

        normalizeConditionLhs(field) {
            return data_get(this.$store.state.publish.base.values, field, undefined);
        },

        normalizeConditionOperator(condition, operator='==') {
            OPERATORS.forEach(op => condition.toString().startsWith(op) ? operator = op : false);

            return operator;
        },

        normalizeConditionRhs(condition) {
            OPERATORS.forEach(op => condition = condition.toString().replace(op, ''));

            return condition.trim();
        },

        passesCondition(condition) {
            let expression = `${condition.lhs} ${condition.operator} ${condition.rhs}`;

            dd(expression);

            return eval(expression);
        },
    }
}
