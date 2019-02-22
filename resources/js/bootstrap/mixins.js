// Mixins
import Vue from 'vue'
import Fieldtype from '../components/fieldtypes/Fieldtype.vue'
import IndexFieldtype from '../components/fieldtypes/IndexFieldtype.vue'

window.Fieldtype = Fieldtype;
window.IndexFieldtype = IndexFieldtype;


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
        },
        $wait(ms) {
            return new Promise(resolve => {
                setTimeout(resolve, ms);
            });
        },
    }
})
