import Validator from '../../components/field-conditions/Validator.js';

export default class {
    showField(field, data) {
        return new Validator(field, data).passesConditions();
    }
}
