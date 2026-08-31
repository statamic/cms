import FieldConditions from './components/FieldConditions.js';
import Passkeys from './components/Passkeys.js';

class Statamic {
    constructor() {
        this.$conditions = new FieldConditions();
        this.$passkeys = new Passkeys();
    }
}

window.Statamic = new Statamic();
