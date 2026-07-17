import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test } from 'vitest';
import ContentDirection from '@/components/ui/ContentDirection.vue';

beforeEach(async () => {
    document.documentElement.setAttribute('dir', 'rtl');
    // Let the ui-direction composable's MutationObserver pick up the attribute change.
    await new Promise((resolve) => queueMicrotask(resolve));
});

afterEach(() => {
    document.documentElement.removeAttribute('dir');
});

test('it renders a div with the content direction by default', () => {
    const wrapper = mount(ContentDirection, {
        slots: { default: 'hello' },
    });

    expect(wrapper.element.tagName).toBe('DIV');
    expect(wrapper.attributes('dir')).toBe('rtl');
});

test('it renders the given "as" element', () => {
    const wrapper = mount(ContentDirection, {
        props: { as: 'span' },
        slots: { default: 'hello' },
    });

    expect(wrapper.element.tagName).toBe('SPAN');
    expect(wrapper.attributes('dir')).toBe('rtl');
});

test('asChild merges the dir attribute onto the slot child instead of wrapping it', () => {
    const wrapper = mount(ContentDirection, {
        props: { asChild: true },
        slots: { default: '<span class="child">hello</span>' },
    });

    expect(wrapper.element.tagName).toBe('SPAN');
    expect(wrapper.classes()).toContain('child');
    expect(wrapper.attributes('dir')).toBe('rtl');
});
