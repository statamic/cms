import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import FormInstancesFieldtype from '@/components/fieldtypes/FormInstancesFieldtype.vue';

const configureMeta = (originValues) => ({ blueprint: {}, meta: {}, originValues, originMeta: {} });

const mountFieldtype = (props = {}) =>
    mount(FormInstancesFieldtype, {
        props: {
            handle: 'form_instances',
            config: { type: 'form_instances' },
            meta: {
                data: [],
                submissions: { contact: { form: 'contact', filters: [], actionUrl: '/' } },
                configureMeta: {
                    contact: configureMeta({ submission_limit: 100, closed_message: null }),
                    feedback: configureMeta({ submission_limit: 10 }),
                },
            },
            value: { forms: ['contact', 'feedback'], config: { contact: { submission_limit: 1 }, feedback: [] } },
            ...props,
        },
        global: {
            stubs: {
                RelationshipFieldtype: true,
                SubmissionsStack: true,
                PublishContainer: true,
                PublishTabs: true,
                Stack: true,
                Button: true,
            },
        },
    });

const formItem = (wrapper) => wrapper.vm.$.provides.formInstancesItem;

test('configure and submissions are offered per form', () => {
    const wrapper = mountFieldtype();

    expect(formItem(wrapper).hasSubmissions({ id: 'contact' })).toBe(true);
    expect(formItem(wrapper).hasSubmissions({ id: 'feedback' })).toBe(false);
    expect(formItem(wrapper).hasConfigure({ id: 'contact' })).toBe(true);
    expect(formItem(wrapper).hasConfigure({ id: 'feedback' })).toBe(true);
    expect(formItem(wrapper).hasConfigure({ id: 'other' })).toBe(false);
});

test('viewing submissions opens the stack for that form', async () => {
    const wrapper = mountFieldtype();

    formItem(wrapper).viewSubmissions({ id: 'contact' });
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.submissionsMeta.form).toBe('contact');
    expect(wrapper.vm.viewingSubmissions).toBe(true);
});

test('submissions are hidden once the form is removed from the field', async () => {
    const wrapper = mountFieldtype();

    formItem(wrapper).viewSubmissions({ id: 'contact' });
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.submissionsMeta.form).toBe('contact');

    await wrapper.setProps({ value: { forms: ['feedback'], config: { feedback: [] } } });

    expect(wrapper.vm.submissionsMeta).toBeNull();
});

test('config is kept for forms that remain when the forms change', async () => {
    const wrapper = mountFieldtype();

    await wrapper.findComponent({ name: 'RelationshipFieldtype' }).vm.$emit('update:value', ['other', 'contact']);

    expect(wrapper.emitted('update:value')[0]).toEqual([
        { forms: ['other', 'contact'], config: { other: {}, contact: { submission_limit: 1 } } },
    ]);
});

test('configuring a form starts from its own overrides', async () => {
    const wrapper = mountFieldtype();

    formItem(wrapper).configure({ id: 'contact' });
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.configOverrides).toEqual({ submission_limit: 1, closed_message: null });
    expect(wrapper.vm.modifiedOverrides).toEqual(['submission_limit']);

    formItem(wrapper).configure({ id: 'feedback' });
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.configOverrides).toEqual({ submission_limit: 10 });
    expect(wrapper.vm.modifiedOverrides).toEqual([]);
});

test('applying only keeps modified fields for the form being configured', async () => {
    const wrapper = mountFieldtype();

    formItem(wrapper).configure({ id: 'contact' });
    await wrapper.vm.$nextTick();

    wrapper.vm.configOverrides.closed_message = 'Full.';
    wrapper.vm.modifiedOverrides = ['closed_message'];
    wrapper.vm.applyConfigure();

    expect(wrapper.emitted('update:value')[0]).toEqual([
        { forms: ['contact', 'feedback'], config: { contact: { closed_message: 'Full.' }, feedback: [] } },
    ]);
    expect(wrapper.vm.configuring).toBe(false);
});

test('a form selected after load can be configured', async () => {
    const wrapper = mountFieldtype({ value: { forms: [], config: {} } });

    await wrapper.findComponent({ name: 'RelationshipFieldtype' }).vm.$emit('update:value', ['feedback']);
    await wrapper.setProps({ value: wrapper.emitted('update:value')[0][0] });

    expect(formItem(wrapper).hasConfigure({ id: 'feedback' })).toBe(true);

    formItem(wrapper).configure({ id: 'feedback' });
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.configuring).toBe(true);
    expect(wrapper.vm.configOverrides).toEqual({ submission_limit: 10 });
});
