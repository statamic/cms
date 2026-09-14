import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import Field from '@/components/ui/Field.vue';

test('it does not set a dir attribute by default', () => {
    const wrapper = mount(Field, {
        props: {
            label: 'Title',
        },
    });

    expect(wrapper.attributes('dir')).toBeUndefined();
});

test('it sets the dir attribute on the root element when provided', () => {
    const wrapper = mount(Field, {
        props: {
            label: 'Title',
            dir: 'rtl',
        },
    });

    expect(wrapper.attributes('dir')).toBe('rtl');
});
