// Mixins
import Vue from 'vue'
import Fieldtype from '../components/fieldtypes/Fieldtype.vue'
import AutoSlug from '../components/fieldtypes/AutoSlug.js'

window.Fieldtype = Fieldtype;
window.AutoSlug = AutoSlug;


Vue.mixin({
    methods: {
        __(key, replacements) {
            return __(key, replacements);
        },
        __n(key, number, replacements) {
            return __n(key, number, replacements);
        }
    }
})
