import { ref, watch } from 'vue';
import { preferences } from '@api';

export const FIELD_NUMBERING_PREFERENCE_KEY = 'forms.field_numbering';

let state = null;

export function useFieldNumberingPreference() {
    if (state) {
        return state;
    }

    const showFieldNumbers = ref(preferences.get(FIELD_NUMBERING_PREFERENCE_KEY, false));

    watch(showFieldNumbers, (value) => {
        preferences.set(FIELD_NUMBERING_PREFERENCE_KEY, value);
    });

    state = { showFieldNumbers };

    return state;
}

export function buildFieldNumbersFromLogicFields(fields) {
    const map = new Map();
    let n = 1;

    (fields || []).forEach((field) => {
        map.set(field.handle, n);

        if (field._id) {
            map.set(field._id, n);
        }

        n++;
    });

    return map;
}

export function buildFieldNumbersFromBuilderPages(pages, fieldsets) {
    const map = new Map();
    let n = 1;
    const fieldsetList = fieldsets
        ? (Array.isArray(fieldsets) ? fieldsets : Object.values(fieldsets))
        : [];

    (pages || []).forEach((page) => {
        (page.sections || []).forEach((section) => {
            (section.fields || []).forEach((fieldConfig) => {
                if (fieldConfig.type === 'link_fields') {
                    return;
                }

                if (fieldConfig.type === 'import') {
                    const fieldset = fieldsetList.find((fs) => fs.handle === fieldConfig.fieldset);

                    (fieldset?.fields || [])
                        .filter((f) => f.type !== 'import')
                        .forEach((ff) => {
                            const handle = (fieldConfig.prefix || '') + ff.handle;
                            map.set(handle, n);
                            map.set(`${fieldConfig._id}:${ff.handle}`, n);
                            n++;
                        });

                    return;
                }

                map.set(fieldConfig._id, n);

                if (fieldConfig.handle) {
                    map.set(fieldConfig.handle, n);
                }

                n++;
            });
        });
    });

    return map;
}

export function fieldNumberFromMap(map, ...keys) {
    for (const key of keys) {
        if (key != null && map.has(key)) {
            return map.get(key);
        }
    }

    return null;
}
