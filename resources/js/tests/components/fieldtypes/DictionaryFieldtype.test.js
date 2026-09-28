import { mount, flushPromises } from '@vue/test-utils';
import { describe, expect, test, vi } from 'vitest';

window.__ = (key) => key;
window.__n = (key) => key;
window.utf8btoa = (string) => btoa(string);

import DictionaryFieldtype from '@/components/fieldtypes/DictionaryFieldtype.vue';

function mountFieldtype({ value, maxItems, selectedOptions, fetchedOptions, shallow = false }) {
    return mount(DictionaryFieldtype, {
        shallow,
        props: {
            handle: 'country',
            value,
            config: { max_items: maxItems },
            meta: { url: '/!/fieldtypes/dictionaries/partial_countries', selectedOptions },
        },
        global: {
            mocks: {
                $axios: { get: vi.fn(() => Promise.resolve({ data: { data: fetchedOptions } })) },
            },
        },
    });
}

describe('DictionaryFieldtype options', () => {
    // API-backed dictionaries only return a page of options, which may not include the stored value.
    // The preloaded selected options must be merged into the options passed to the Combobox so it can
    // resolve their labels — otherwise a single-select field displays the raw id instead.
    // https://github.com/statamic/cms/issues/14835
    test('single-select renders the label of a stored value missing from the fetched options', async () => {
        const fieldtype = mountFieldtype({
            value: 'de',
            maxItems: 1,
            selectedOptions: [{ value: 'de', label: 'Germany', invalid: false }],
            fetchedOptions: [],
        });
        await flushPromises();

        const selected = fieldtype.find('[data-ui-combobox-selected-option]');
        expect(selected.text()).toContain('Germany');
        expect(selected.text()).not.toContain('de');
    });

    test('selected options already present in the fetched options are not duplicated', async () => {
        const fieldtype = mountFieldtype({
            value: 'de',
            maxItems: 1,
            selectedOptions: [{ value: 'de', label: 'Germany', invalid: false }],
            fetchedOptions: [{ value: 'de', label: 'Germany' }],
            shallow: true,
        });
        await flushPromises();

        expect(fieldtype.vm.normalizedOptions).toEqual([{ value: 'de', label: 'Germany' }]);
    });

    test('multi-select options are not polluted with selected options', async () => {
        const fieldtype = mountFieldtype({
            value: ['de'],
            maxItems: null,
            selectedOptions: [{ value: 'de', label: 'Germany', invalid: false }],
            fetchedOptions: [{ value: 'ca', label: 'Canada' }],
            shallow: true,
        });
        await flushPromises();

        expect(fieldtype.vm.normalizedOptions).toEqual([{ value: 'ca', label: 'Canada' }]);
    });

    test('selected options carry their icon when present', async () => {
        const fieldtype = mountFieldtype({
            value: ['de', 'fr'],
            maxItems: null,
            selectedOptions: [
                { value: 'de', label: 'Germany', icon: 'globe', invalid: false },
                { value: 'fr', label: 'France', invalid: false },
            ],
            fetchedOptions: [],
            shallow: true,
        });
        await flushPromises();

        expect(fieldtype.vm.selectedOptions).toEqual([
            { value: 'de', label: 'Germany', icon: 'globe', invalid: false },
            { value: 'fr', label: 'France', invalid: false },
        ]);
    });
});
