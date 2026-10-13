import { mount } from '@vue/test-utils';
import { expect, test, vi, afterEach } from 'vitest';
import Switch from '@/components/ui/Switch.vue';

afterEach(() => {
    vi.restoreAllMocks();
});

test('the label prop is rendered as an aria-label on the switch element', () => {
    const wrapper = mount(Switch, {
        props: { label: 'Publish this entry' },
    });

    expect(wrapper.find('[role="switch"]').attributes('aria-label')).toBe('Publish this entry');
});

test('an aria-label attribute falls through to the switch element', () => {
    const wrapper = mount(Switch, {
        attrs: { 'aria-label': 'Publish this entry' },
    });

    expect(wrapper.find('[role="switch"]').attributes('aria-label')).toBe('Publish this entry');
});

test('no aria-label is rendered when neither label nor aria-label is given', () => {
    const wrapper = mount(Switch);

    expect(wrapper.find('[role="switch"]').attributes('aria-label')).toBeUndefined();
});

test('it warns in development when no accessible name is provided', () => {
    const warn = vi.spyOn(console, 'warn').mockImplementation(() => {});

    mount(Switch);

    expect(warn).toHaveBeenCalledWith(
        expect.stringContaining('[ui/Switch] Provide a `label` prop'),
    );
});
