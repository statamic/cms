// Mixins
import Vue from 'vue'
import Fieldtype from '../components/fieldtypes/Fieldtype.vue'

window.Fieldtype = Fieldtype;


Vue.mixin({
    methods: {
        __(key, replacements) {
            return __(key, replacements);
        },
        __n(key, number, replacements) {
            return __n(key, number, replacements);
        },
        translate(key, replacements) { // TODO: Remove
            return __(key, replacements);
        }
    }
})
