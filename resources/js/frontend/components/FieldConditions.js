import Validator from '../../components/field-conditions/Validator.js';

export default class {
    showField(conditions, data, currentFieldPath) {
        return new Validator(conditions, data, currentFieldPath).passesConditions();
    }
}
