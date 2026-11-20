import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import Condition from '@/components/field-conditions/Condition.vue';
import { Combobox } from '@/components/ui';

globalThis.__ = (key) => key;

const mountCondition = (field, condition = {}, slots = {}) =>
    mount(Condition, {
        props: {
            condition: { field: field.handle, operator: 'equals', value: null, ...condition },
            suggestableFields: [field],
        },
        slots,
    });

const valueCombobox = (wrapper) => wrapper.findAllComponents(Combobox).at(2);

test('option values are stored as strings', async () => {
    const wrapper = mountCondition({
        handle: 'rating',
        config: { type: 'select', options: [{ key: 1, value: 'One' }, { key: 0, value: 'Zero' }] },
    });

    valueCombobox(wrapper).vm.$emit('update:modelValue', 0);

    expect(wrapper.emitted('update:condition').at(-1)[0].value).toBe('0');
});

test('yes/no fields offer yes and no options instead of a toggle', () => {
    const wrapper = mountCondition({ handle: 'agree', config: { type: 'yes_no' } });

    expect(wrapper.findAllComponents(Combobox)).toHaveLength(3);
    expect(valueCombobox(wrapper).props('options')).toEqual([
        { value: 'yes', label: 'Yes' },
        { value: 'no', label: 'No' },
    ]);
});

test('picking a yes/no field does not preset the value to false', () => {
    const field = { handle: 'agree', config: { type: 'yes_no' } };
    const wrapper = mountCondition(field, { field: null });

    wrapper.findAllComponents(Combobox).at(0).vm.$emit('update:modelValue', 'agree');

    expect(wrapper.emitted('update:condition').at(-1)[0].value).toBeNull();
});

test('star rating fields use a number input', () => {
    const wrapper = mountCondition({ handle: 'stars', config: { type: 'star_rating' } });

    expect(wrapper.find('input[type="number"]').exists()).toBe(true);
});

test('the field picker only hides its input while closed when a selected field slot is given', () => {
    const field = { handle: 'name', config: { type: 'text' } };

    expect(mountCondition(field).findAllComponents(Combobox).at(0).props('hideInputWhenClosed')).toBe(false);
    expect(
        mountCondition(field, {}, { 'field-selected': '<span />' }).findAllComponents(Combobox).at(0).props('hideInputWhenClosed'),
    ).toBe(true);
});
