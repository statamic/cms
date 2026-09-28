import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { h } from 'vue';
import RadioGroup from '@/components/ui/Radio/Group.vue';
import Radio from '@/components/ui/Radio/Item.vue';

// RadioGroupItem requires a RadioGroupRoot ancestor for its reka-ui context injection.
function mountRadio(props) {
    return mount(RadioGroup, {
        slots: {
            default: () => h(Radio, { label: 'Yes', value: 'yes', ...props }),
        },
    });
}

test('the label targets the auto-generated id when no id prop is given', () => {
    const wrapper = mountRadio({});

    const id = wrapper.find('[role="radio"]').attributes('id');

    expect(id).toBeTruthy();
    expect(wrapper.find('label').attributes('for')).toBe(id);
});

test('a custom id prop is honored on the control and the label', () => {
    const wrapper = mountRadio({ id: 'custom-radio-id' });

    expect(wrapper.find('[role="radio"]').attributes('id')).toBe('custom-radio-id');
    expect(wrapper.find('label').attributes('for')).toBe('custom-radio-id');
});

test('aria-describedby and the description element share the custom id', () => {
    const wrapper = mountRadio({ id: 'custom-radio-id', description: 'This cannot be undone' });

    expect(wrapper.find('[role="radio"]').attributes('aria-describedby')).toBe('custom-radio-id-description');
    expect(wrapper.find('span').attributes('id')).toBe('custom-radio-id-description');
});
