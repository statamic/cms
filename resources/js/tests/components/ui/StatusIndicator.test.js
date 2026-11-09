import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { StatusIndicator } from '@/components/ui';

window.__ = (key) => key;

test('the dot has a text alternative when the label is hidden', () => {
    const wrapper = mount(StatusIndicator, {
        props: { status: 'draft' },
    });

    expect(wrapper.text()).toBe('Draft');
});

test('the label is not duplicated when it is shown', () => {
    const wrapper = mount(StatusIndicator, {
        props: { status: 'draft', showLabel: true },
    });

    expect(wrapper.text()).toBe('Draft');
});

test('the hidden status has a label', () => {
    const wrapper = mount(StatusIndicator, {
        props: { status: 'hidden' },
    });

    expect(wrapper.text()).toBe('Hidden');
});
