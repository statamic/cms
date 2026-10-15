import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import FormFieldtype from '@/components/fieldtypes/FormFieldtype.vue';

const mountFieldtype = (props = {}) =>
    mount(FormFieldtype, {
        props: {
            handle: 'rsvp_form',
            config: { type: 'form', max_items: 1 },
            meta: { data: [] },
            value: ['contact'],
            ...props,
        },
        global: {
            stubs: {
                RelationshipFieldtype: true,
                SubmissionsStack: true,
            },
        },
    });

test('the value is passed through as plain handles', async () => {
    const wrapper = mountFieldtype();

    await wrapper.findComponent({ name: 'RelationshipFieldtype' }).vm.$emit('update:value', ['other']);

    expect(wrapper.emitted('update:value')[0]).toEqual([['other']]);
});

test('items only have submissions when the form matches the submissions meta', () => {
    const wrapper = mountFieldtype({
        meta: { data: [], submissions: { form: 'contact', filters: [], actionUrl: '/' } },
    });

    const formItem = wrapper.vm.$.provides.formFieldtypeItem;

    expect(formItem.hasSubmissions({ id: 'contact' })).toBe(true);
    expect(formItem.hasSubmissions({ id: 'other' })).toBe(false);
    expect(formItem.hasConfigure({ id: 'contact' })).toBe(false);
});

test('submissions are hidden once the form is unlinked', () => {
    const wrapper = mountFieldtype({
        value: [],
        meta: { data: [], submissions: { form: 'contact', filters: [], actionUrl: '/' } },
    });

    expect(wrapper.vm.submissionsMeta).toBeNull();
});
