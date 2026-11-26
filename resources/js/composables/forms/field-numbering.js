import { ref, watch } from 'vue';
import { preferences } from '@api';

let state = null;

export function useFieldNumberingPreference() {
    if (! state) {
        const showFieldNumbers = ref(preferences.get('forms.field_numbering', false));
        watch(showFieldNumbers, (value) => preferences.set('forms.field_numbering', value));
        state = { showFieldNumbers };
    }

    return state;
}
