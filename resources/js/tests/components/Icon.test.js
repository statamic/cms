import { flushPromises, mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import Icon from '@/components/ui/Icon/Icon.vue';
import { registerIconSetFromStrings } from '@/components/ui/Icon/registry.js';

registerIconSetFromStrings('icon-test-alternate', {
    foo: '<svg><title>Alternate Foo</title></svg>',
});

registerIconSetFromStrings('icon-test-ignored', {
    foo: '<svg><title>Ignored Foo</title></svg>',
});

test('it can resolve the icon set from the name', async () => {
    const wrapper = mount(Icon, {
        props: {
            name: 'icon-test-alternate::foo',
        },
    });

    await flushPromises();

    expect(wrapper.find('title').text()).toBe('Alternate Foo');
});

test('the icon set from the name takes precedence over the set prop', async () => {
    const warn = vi.spyOn(console, 'warn').mockImplementation(() => {});

    const wrapper = mount(Icon, {
        props: {
            name: 'icon-test-alternate::foo',
            set: 'icon-test-ignored',
        },
    });

    await flushPromises();

    expect(wrapper.find('title').text()).toBe('Alternate Foo');
    expect(warn).toHaveBeenCalledWith('Icon name [icon-test-alternate::foo] includes set [icon-test-alternate], ignoring set prop [icon-test-ignored]');

    warn.mockRestore();
});
