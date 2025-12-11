import { ref } from 'vue';

const conditions = ref({});

export default class FieldConditions {
    add(name, condition) {
        conditions.value[name] = condition;
    }

    get(name) {
        return conditions.value[name];
    }
}
