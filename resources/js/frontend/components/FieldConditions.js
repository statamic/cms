import Validator from '../../components/field-conditions/Validator.js';

export default class {
    showField(conditions, data, currentFieldPath) {
        return new Validator(conditions, data, null, currentFieldPath).usingRootValues().passesConditions();
    }
}
