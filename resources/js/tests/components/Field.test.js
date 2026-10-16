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

test('it associates errors and instructions with the nested input', () => {
    const wrapper = mount(Field, {
        props: {
            label: 'Email',
            instructions: 'Your work email',
            error: 'This field is required.',
        },
        slots: {
            default: () => h(Input, { name: 'email' }),
        },
        attachTo: document.body,
    });

    const input = wrapper.find('[data-ui-control]');
    const description = wrapper.find('[data-ui-description]');
    const error = wrapper.find('[data-ui-error-message]');

    expect(description.attributes('id')).toBeTruthy();
    expect(error.attributes('id')).toBe(`${input.attributes('id')}-error`);
    expect(input.attributes('aria-invalid')).toBe('true');
    expect(input.attributes('aria-describedby')).toContain(description.attributes('id'));
    expect(input.attributes('aria-describedby')).toContain(error.attributes('id'));

    wrapper.unmount();
});

test('an explicit field id still wires describedby and invalid onto nested inputs', () => {
    const wrapper = mount(Field, {
        props: {
            label: 'Title',
            id: 'field_title',
            error: 'This field is required.',
        },
        slots: {
            default: () => h(Input, { name: 'title', id: 'field_title' }),
        },
        attachTo: document.body,
    });

    const input = wrapper.find('[data-ui-control]');
    const error = wrapper.find('[data-ui-error-message]');

    expect(input.attributes('id')).toBe('field_title');
    expect(error.attributes('id')).toBe('field_title-error');
    expect(input.attributes('aria-invalid')).toBe('true');
    expect(input.attributes('aria-describedby')).toBe('field_title-error');

    wrapper.unmount();
});

test('descendant controls that do not share the field id are not marked invalid', () => {
    const wrapper = mount(Field, {
        props: {
            label: 'Table',
            id: 'field_table',
            error: 'This field is required.',
        },
        slots: {
            default: () => [h(Input, { name: 'a' }), h(Input, { name: 'b' })],
        },
        attachTo: document.body,
    });

    const inputs = wrapper.findAll('[data-ui-control]');

    expect(inputs[0].attributes('aria-invalid')).toBeUndefined();
    expect(inputs[1].attributes('aria-invalid')).toBeUndefined();
    expect(inputs[0].attributes('aria-describedby')).toBeUndefined();
    expect(inputs[1].attributes('aria-describedby')).toBeUndefined();

    wrapper.unmount();
});

test('it assigns stable ids across multiple errors', () => {
    const wrapper = mount(Field, {
        props: {
            label: 'Email',
            id: 'field_email',
            errors: ['Required.', 'Must be valid.'],
        },
        slots: {
            default: () => h(Input, { name: 'email', id: 'field_email' }),
        },
        attachTo: document.body,
    });

    const errors = wrapper.findAll('[data-ui-error-message]');
    const input = wrapper.find('[data-ui-control]');

    expect(errors[0].attributes('id')).toBe('field_email-error');
    expect(errors[1].attributes('id')).toBe('field_email-error-1');
    expect(input.attributes('aria-describedby')).toBe('field_email-error field_email-error-1');

    wrapper.unmount();
});

test('it does not set aria-describedby when the field has no instructions or errors', () => {
    const wrapper = mount(Field, {
        props: { label: 'Email' },
        slots: {
            default: () => h(Input, { name: 'email' }),
        },
        attachTo: document.body,
    });

    expect(wrapper.find('[data-ui-control]').attributes('aria-describedby')).toBeUndefined();

    wrapper.unmount();
});

test('it merges consumer aria-describedby with the field description', () => {
    const wrapper = mount(Field, {
        props: {
            label: 'Email',
            id: 'field_email',
            error: 'Required.',
        },
        slots: {
            default: () => h(Input, {
                name: 'email',
                id: 'field_email',
                inputAttrs: { 'aria-describedby': 'extra-help' },
            }),
        },
        attachTo: document.body,
    });

    expect(wrapper.find('[data-ui-control]').attributes('aria-describedby')).toBe(
        'field_email-error extra-help',
    );

    wrapper.unmount();
});
