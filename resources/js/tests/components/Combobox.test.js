import { mount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';

globalThis.__ = (key) => key;
globalThis.__n = (key) => key;

import Combobox from '@/components/ui/Combobox/Combobox.vue';

const OPTIONS = [
    { label: 'Day(s)', value: 3 },
    { label: 'Week(s)', value: 2 },
    { label: 'Month(s)', value: 1 },
    { label: 'Year(s)', value: 0 },
];

function mountCombobox(props = {}) {
    return mount(Combobox, {
        props: {
            options: OPTIONS,
            searchable: false,
            clearable: false,
            ...props,
        },
    });
}

describe('Combobox falsy modelValue', () => {
    test('shows the selected label when modelValue is 0', () => {
        const wrapper = mountCombobox({ modelValue: 0 });

        expect(wrapper.get('[data-ui-combobox-selected-option]').text()).toBe('Year(s)');
    });

    test('still shows the selected label for a truthy modelValue', () => {
        const wrapper = mountCombobox({ modelValue: 2 });

        expect(wrapper.get('[data-ui-combobox-selected-option]').text()).toBe('Week(s)');
    });

    test('shows the placeholder when nothing is selected', () => {
        const wrapper = mountCombobox({ modelValue: null, placeholder: 'Select...' });

        expect(wrapper.get('[data-ui-combobox-selected-option]').text()).toBe('Select...');
    });

    test('the clear button appears for a 0 modelValue when clearable', () => {
        const wrapper = mountCombobox({ modelValue: 0, clearable: true });

        expect(wrapper.find('[data-ui-combobox-clear-button]').exists()).toBe(true);
    });
});
