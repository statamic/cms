import Validator from '../../components/field-conditions/Validator.js';

export default class {
    showField(currentFieldPath, conditions, data) {
        return new Validator(currentFieldPath, conditions, data).passesConditions();
    }
}
