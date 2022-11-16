import FieldConditions from './components/FieldConditions.js';

class Statamic {
    constructor() {
        this.$conditions = new FieldConditions;
    }
}

window.Statamic = new Statamic;
