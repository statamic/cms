import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { h } from 'vue';
import Field from '@/components/ui/Field.vue';
import Input from '@/components/ui/Input/Input.vue';
import Switch from '@/components/ui/Switch.vue';
import Combobox from '@/components/ui/Combobox/Combobox.vue';

window.__ = (key) => key;

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

test('it associates the label with a nested input when id is omitted', () => {
    const wrapper = mount(Field, {
        props: { label: 'Email' },
        slots: {
            default: () => h(Input, { name: 'email' }),
        },
        attachTo: document.body,
    });

    const label = wrapper.find('[data-ui-label]');
    const input = wrapper.find('[data-ui-control]');

    expect(label.attributes('for')).toBeTruthy();
    expect(input.attributes('id')).toBe(label.attributes('for'));

    wrapper.unmount();
});

test('an explicit field id is used for the label for attribute', () => {
    const wrapper = mount(Field, {
        props: { label: 'Email', id: 'login-email' },
        slots: {
            default: () => h(Input, { name: 'email' }),
        },
        attachTo: document.body,
    });

    const label = wrapper.find('[data-ui-label]');

    expect(label.attributes('for')).toBe('login-email');
    expect(label.attributes('id')).toBe('login-email-label');

    wrapper.unmount();
});

test('it associates the label with a nested switch when id is omitted', () => {
    const wrapper = mount(Field, {
        props: { label: 'Required' },
        slots: {
            default: () => h(Switch),
        },
        attachTo: document.body,
    });

    const label = wrapper.find('[data-ui-label]');
    const control = wrapper.find('[data-ui-control]');

    expect(label.attributes('for')).toBeTruthy();
    expect(control.attributes('id')).toBe(label.attributes('for'));

    wrapper.unmount();
});

test('it lets only the first nested control claim an auto-generated field id', () => {
    const wrapper = mount(Field, {
        props: { label: 'Pair' },
        slots: {
            default: () => [h(Input, { name: 'a' }), h(Input, { name: 'b' })],
        },
        attachTo: document.body,
    });

    const label = wrapper.find('[data-ui-label]');
    const inputs = wrapper.findAll('[data-ui-control]');

    expect(inputs).toHaveLength(2);
    expect(inputs[0].attributes('id')).toBe(label.attributes('for'));
    expect(inputs[1].attributes('id')).toBeTruthy();
    expect(inputs[1].attributes('id')).not.toBe(label.attributes('for'));

    wrapper.unmount();
});

test('an explicit control id wins over an auto-generated field id', () => {
    const wrapper = mount(Field, {
        props: { label: 'Email' },
        slots: {
            default: () => h(Input, { name: 'email', id: 'custom-input' }),
        },
        attachTo: document.body,
    });

    const input = wrapper.find('[data-ui-control]');

    expect(input.attributes('id')).toBe('custom-input');

    wrapper.unmount();
});

test('an explicit field id is not injected into nested controls', () => {
    const wrapper = mount(Field, {
        props: { label: 'Title', id: 'field_title' },
        slots: {
            default: () => [h(Input, { name: 'a' }), h(Input, { name: 'b' })],
        },
        attachTo: document.body,
    });

    const label = wrapper.find('[data-ui-label]');
    const inputs = wrapper.findAll('[data-ui-control]');

    expect(label.attributes('for')).toBe('field_title');
    expect(inputs[0].attributes('id')).not.toBe('field_title');
    expect(inputs[1].attributes('id')).not.toBe('field_title');
    expect(inputs[0].attributes('id')).not.toBe(inputs[1].attributes('id'));

    wrapper.unmount();
});

test('a standalone input still gets a unique generated id', () => {
    const wrapper = mount(Input, {
        props: { name: 'email' },
        attachTo: document.body,
    });

    expect(wrapper.find('[data-ui-control]').attributes('id')).toBeTruthy();

    wrapper.unmount();
});

test('it associates the label with a nested combobox trigger via aria-labelledby when id is omitted', () => {
    const wrapper = mount(Field, {
        props: { label: 'Format' },
        slots: {
            default: () => h(Combobox, {
                options: [{ label: 'One', value: 'one' }],
                modelValue: null,
                searchable: false,
                placeholder: 'Select...',
            }),
        },
        attachTo: document.body,
    });

    const label = wrapper.find('[data-ui-label]');
    const trigger = wrapper.find('[data-ui-combobox-trigger]');

    expect(label.attributes('id')).toBeTruthy();
    expect(trigger.attributes('id')).toBe(label.attributes('for'));
    expect(trigger.attributes('aria-labelledby')).toBe(label.attributes('id'));

    wrapper.unmount();
});
