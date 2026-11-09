import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import Checkbox from '@/components/ui/Checkbox/Item.vue';

test('the label targets the auto-generated id when no id prop is given', () => {
    const wrapper = mount(Checkbox, {
        props: { label: 'Subscribe' },
    });

    const id = wrapper.find('[role="checkbox"]').attributes('id');

    expect(id).toBeTruthy();
    expect(wrapper.find('label').attributes('for')).toBe(id);
});

test('a custom id prop is honored on the control and the label', () => {
    const wrapper = mount(Checkbox, {
        props: { label: 'Subscribe', id: 'custom-checkbox-id' },
    });

    expect(wrapper.find('[role="checkbox"]').attributes('id')).toBe('custom-checkbox-id');
    expect(wrapper.find('label').attributes('for')).toBe('custom-checkbox-id');
});

test('aria-describedby and the description element share the custom id', () => {
    const wrapper = mount(Checkbox, {
        props: { label: 'Subscribe', id: 'custom-checkbox-id', description: 'Receive occasional emails' },
    });

    expect(wrapper.find('[role="checkbox"]').attributes('aria-describedby')).toBe('custom-checkbox-id-description');
    expect(wrapper.find('p').attributes('id')).toBe('custom-checkbox-id-description');
});

test('the label prop is rendered as the aria-label', () => {
    const wrapper = mount(Checkbox, {
        props: { label: 'Subscribe' },
    });

    expect(wrapper.find('[role="checkbox"]').attributes('aria-label')).toBe('Subscribe');
});

test('no aria-label is rendered when the label comes from the slot', () => {
    const wrapper = mount(Checkbox, {
        props: { value: 'subscribe' },
        slots: { default: () => 'Subscribe' },
    });

    expect(wrapper.find('[role="checkbox"]').attributes('aria-label')).toBeUndefined();
});

test('indeterminate sets data-state and shows the dash indicator', () => {
    const wrapper = mount(Checkbox, {
        props: { label: 'Select items', modelValue: false, indeterminate: true },
    });

    const root = wrapper.find('[role="checkbox"]');

    expect(root.attributes('data-state')).toBe('indeterminate');
    expect(root.attributes('aria-checked')).toBe('mixed');
    expect(wrapper.find('svg path').attributes('d')).toBe('M2 1H8');
});

