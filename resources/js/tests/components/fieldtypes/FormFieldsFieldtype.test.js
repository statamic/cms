import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import FormFieldsFieldtype from '@/components/fieldtypes/FormFieldsFieldtype.vue';
import { Combobox } from '@/components/ui';

window.__ = (key) => key;

function type(values) {
    const wrapper = mount(FormFieldsFieldtype, {
        shallow: true,
        props: {
            handle: 'to',
            value: [],
            config: { taggable: true, multiple: true },
            meta: { options: [{ value: 'field:email', label: 'Email Address', handle: 'email' }] },
        },
    });

    wrapper.findComponent(Combobox).vm.$emit('update:modelValue', values);

    return wrapper.emitted('update:value').at(-1)[0];
}

/** @see https://github.com/statamic/cms/issues/16049 */
test('typing a field handle references the field', () => {
    expect(type(['email'])).toEqual(['field:email']);
});

test('typing an email address keeps it as is', () => {
    expect(type(['hello@example.com'])).toEqual(['hello@example.com']);
});
