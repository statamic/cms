import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import WidthSelector from '@/components/fields/WidthSelector.vue';

function states(wrapper) {
    return wrapper.findAll('[data-state]').map((el) => el.attributes('data-state'));
}

function label(wrapper) {
    return wrapper.find('.pointer-events-none').text();
}

test('it fills the stops up to the selected percentage', () => {
    const wrapper = mount(WidthSelector, { props: { modelValue: 50 } });

    expect(states(wrapper)).toEqual(['selected', 'selected', 'selected', 'unselected', 'unselected', 'unselected']);
    expect(label(wrapper)).toBe('50%');
});

test('it fills the stops for a width the stops do not offer', () => {
    const wrapper = mount(WidthSelector, { props: { modelValue: 6 } });

    expect(states(wrapper)).toEqual(['selected', 'selected', 'selected', 'unselected', 'unselected', 'unselected']);
    expect(label(wrapper)).toBe('50%');
});

test('it fills every stop at full width', () => {
    const wrapper = mount(WidthSelector, { props: { modelValue: 12 } });

    expect(states(wrapper)).toEqual(Array(6).fill('selected'));
    expect(label(wrapper)).toBe('100%');
});

test('it marks the last filled stop unless it is full width', () => {
    expect(mount(WidthSelector, { props: { modelValue: 6 } }).findAll('[data-last="true"]')).toHaveLength(1);
    expect(mount(WidthSelector, { props: { modelValue: 12 } }).findAll('[data-last="true"]')).toHaveLength(0);
});

test('it emits the raw stop value when clicked', async () => {
    const wrapper = mount(WidthSelector, { props: { modelValue: 6 } });

    await wrapper.findAll('[data-state]')[2].trigger('click');

    expect(wrapper.emitted('update:model-value')).toEqual([[50]]);
});
