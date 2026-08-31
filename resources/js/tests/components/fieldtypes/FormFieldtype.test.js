import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import FormFieldtype from '@/components/fieldtypes/FormFieldtype.vue';

const mountFieldtype = (props = {}) =>
    mount(FormFieldtype, {
        props: {
            handle: 'rsvp_form',
            config: { type: 'form', max_items: 1 },
            meta: { configurable: true, data: [] },
            value: { form: ['contact'], config: { submission_limit: 1 } },
            ...props,
        },
        global: {
            stubs: {
                RelationshipFieldtype: true,
                SubmissionListing: true,
                InlineSubmissionForm: true,
                PublishContainer: true,
                PublishTabs: true,
                Stack: true,
                Button: true,
            },
        },
    });

test('config is kept when the form is unchanged', () => {
    const wrapper = mountFieldtype();

    wrapper.vm.formUpdated(['contact']);

    expect(wrapper.emitted('update:value')[0]).toEqual([{ form: ['contact'], config: { submission_limit: 1 } }]);
});

test('config is cleared when the form changes', () => {
    const wrapper = mountFieldtype();

    wrapper.vm.formUpdated(['other']);

    expect(wrapper.emitted('update:value')[0]).toEqual([{ form: ['other'], config: {} }]);
});

test('plain handles are used when not configurable', () => {
    const wrapper = mountFieldtype({
        meta: { configurable: false, data: [] },
        value: ['contact'],
    });

    wrapper.vm.formUpdated(['other']);

    expect(wrapper.emitted('update:value')[0]).toEqual([['other']]);
});

test('only modified fields are persisted as overrides', async () => {
    const wrapper = mountFieldtype({
        meta: {
            configurable: true,
            data: [],
            configureMeta: {
                form: 'contact',
                blueprint: {},
                values: {},
                meta: {},
                originValues: { submission_limit: 100, closed_message: null },
                originMeta: {},
            },
        },
    });

    wrapper.vm.configuringForm = true;
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.configOverrides).toEqual({ submission_limit: 1, closed_message: null });
    expect(wrapper.vm.modifiedOverrides).toEqual(['submission_limit']);

    wrapper.vm.configOverrides.closed_message = 'Full.';
    wrapper.vm.modifiedOverrides.push('closed_message');
    wrapper.vm.applyConfigure();

    expect(wrapper.emitted('update:value')[0]).toEqual([
        { form: ['contact'], config: { submission_limit: 1, closed_message: 'Full.' } },
    ]);
});

test('synced fields are dropped from the overrides', async () => {
    const wrapper = mountFieldtype({
        meta: {
            configurable: true,
            data: [],
            configureMeta: {
                form: 'contact',
                blueprint: {},
                values: {},
                meta: {},
                originValues: { submission_limit: 100 },
                originMeta: {},
            },
        },
    });

    wrapper.vm.configuringForm = true;
    await wrapper.vm.$nextTick();

    wrapper.vm.modifiedOverrides = [];
    wrapper.vm.applyConfigure();

    expect(wrapper.emitted('update:value')[0]).toEqual([{ form: ['contact'], config: {} }]);
});
