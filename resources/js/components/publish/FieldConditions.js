const OPERATORS = ['==', '!=', '===', '!==', '>', '>=', '<', '<=', 'is', 'equals', 'not'];

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
            let lhs = data_get(this.$store.state.publish.base.values, field, undefined);

            if (_.isString(lhs)) {
                lhs = JSON.stringify(lhs.trim());
            }

            return lhs;
        },

        normalizeConditionOperator(condition, operator='==') {
            OPERATORS.forEach(value => condition.toString().startsWith(value + ' ') ? operator = value : false);

            switch (operator) {
                case 'is':
                case 'equals':
                    operator = '==';
                    break;
                case 'not':
                    operator = '!=';
                    break;
            }

            return operator;
        },

        normalizeConditionRhs(condition) {
            let rhs = condition;

            OPERATORS.forEach(value => rhs = rhs.toString().replace(new RegExp(`^${value} `), ''));

            switch (rhs) {
                case 'null':
                case 'empty':
                    rhs = null;
                    break;
            }

            if (_.isString(rhs)) {
                rhs = JSON.stringify(rhs.trim());
            }

            return rhs;
        },

        passesCondition(condition) {
            return eval(`${condition.lhs} ${condition.operator} ${condition.rhs}`);
        },
    }
}
