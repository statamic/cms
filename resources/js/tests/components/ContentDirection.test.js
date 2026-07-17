import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import ContentDirection from '@/components/ui/ContentDirection.vue';

test('it renders a div with the content direction by default', async () => {
    document.documentElement.setAttribute('dir', 'rtl');
    // Let the ui-direction composable's MutationObserver pick up the attribute change.
    await new Promise((resolve) => queueMicrotask(resolve));

    const wrapper = mount(ContentDirection, {
        slots: { default: 'hello' },
    });

    expect(wrapper.element.tagName).toBe('DIV');
    expect(wrapper.attributes('dir')).toBe('rtl');
});

test('it renders the given "as" element', () => {
    document.documentElement.setAttribute('dir', 'rtl');

    const wrapper = mount(ContentDirection, {
        props: { as: 'span' },
        slots: { default: 'hello' },
    });

    expect(wrapper.element.tagName).toBe('SPAN');
    expect(wrapper.attributes('dir')).toBe('rtl');
});

test('asChild merges the dir attribute onto the slot child instead of wrapping it', () => {
    document.documentElement.setAttribute('dir', 'rtl');

    const wrapper = mount(ContentDirection, {
        props: { asChild: true },
        slots: { default: '<span class="child">hello</span>' },
    });

    expect(wrapper.element.tagName).toBe('SPAN');
    expect(wrapper.classes()).toContain('child');
    expect(wrapper.attributes('dir')).toBe('rtl');
});
