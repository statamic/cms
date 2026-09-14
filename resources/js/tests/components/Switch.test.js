import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import Switch from '@/components/ui/Switch.vue';

test('an aria-label attribute falls through to the switch element', () => {
    const wrapper = mount(Switch, {
        attrs: { 'aria-label': 'Publish this entry' },
    });

    expect(wrapper.find('[role="switch"]').attributes('aria-label')).toBe('Publish this entry');
});

test('no aria-label is rendered when no aria-label attribute is given', () => {
    const wrapper = mount(Switch);

    expect(wrapper.find('[role="switch"]').attributes('aria-label')).toBeUndefined();
});
