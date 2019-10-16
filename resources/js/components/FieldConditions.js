export default class FieldConditions {

    add(name, condition) {
        Statamic.$store.commit('statamic/condition', {name, condition});
    }

}
