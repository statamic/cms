import { flushPromises, mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import { h, inject } from 'vue';
import * as Globals from '@/bootstrap/globals';
import Settings from '@/components/fields/Settings.vue';
import Section from '@/components/blueprints/Section.vue';
import GridFields from '@/components/fieldtypes/grid/FieldsFieldtype.vue';
import { publishContextKey } from '@/components/ui';

Object.assign(window, Globals);
window.__ = key => key;
window.cp_url = url => url;
window.Statamic = { $config: { get: key => key === 'sites' ? [] : undefined }, $fieldActions: { get: () => [] } };

const Probe = {
    methods: {
        clearDirtyState() {},
    },
    setup() {
        const context = inject('fieldtypeContext');
        return () => h('div', [h('output', { class: 'blueprint-section-draggable-zone' }, JSON.stringify(context.value ?? context))]);
    },
};
const PassThrough = { template: '<div><slot /><slot name="actions" /></div>' };

test.each([
    [{ view: 'blueprint', blueprint: 'runway::post', field_path: [], nested: false }, 'author'],
    [{ view: 'fieldset', fieldset: 'common', field_path: [], nested: false }, null],
])('settings send and inject owner context independently of configuration values', async (context, handle) => {
    const post = vi.fn((url, payload) => Promise.resolve({ data: url === 'fields/edit' ? {
        fieldtype: { icon: 'text' },
        blueprint: { tabs: [{ sections: [] }] },
        values: payload.values,
        meta: {},
    } : payload.values }));
    const wrapper = mount(Settings, {
        props: { config: { handle, type: 'text' }, fields: [], type: 'text' },
        global: {
            provide: { fieldtypeContext: context },
            mocks: {
                $axios: { post },
                $keys: { bindGlobal: () => ({ destroy() {} }) },
                $config: { get: () => undefined },
            },
            stubs: {
                StackHeader: PassThrough, StackContent: PassThrough,
                Tabs: PassThrough, TabContent: PassThrough, TabList: true, Button: true,
                CardPanel: true, FieldConditionsBuilder: true, FieldValidationBuilder: true,
            },
            components: { 'ui-publish-container': Probe },
        },
    });
    await flushPromises();

    expect(JSON.parse(wrapper.find('output').text())).toEqual({ ...context, field_path: [handle] });
    expect(post.mock.calls[0][1].context).toEqual({ ...context, field_path: [handle] });
    wrapper.vm.values.handle = 'renamed';
    await flushPromises();
    expect(JSON.parse(wrapper.find('output').text()).field_path).toEqual(['renamed']);

    await wrapper.findAllComponents({ name: 'Button' })[0].trigger('click');
    await flushPromises();
    const payload = post.mock.calls.at(-1)[1];
    expect(payload.context.field_path).toEqual(['renamed']);
    expect(payload.values).not.toHaveProperty('context');
    wrapper.unmount();
});

test.each([false, true])('sections add set handles only inside sets (inside set: %s)', insideSet => {
    const wrapper = mount(Section, {
        props: { section: { handle: 'quote', fields: [] } },
        global: {
            provide: {
                fieldtypeContext: { blueprint: 'runway::post', field_path: ['blocks'], nested: false },
                isInsideSet: insideSet,
            },
            mocks: { $config: { get: () => undefined } },
            stubs: { Fields: Probe, 'ui-panel': PassThrough, 'ui-panel-header': true, 'ui-stack': true },
        },
    });
    expect(JSON.parse(wrapper.find('output').text())).toEqual({
        blueprint: 'runway::post',
        field_path: insideSet ? ['blocks', 'quote'] : ['blocks'],
        nested: insideSet,
    });
    wrapper.unmount();
});

test('grid field configuration marks its children nested without adding a config-field handle', () => {
    const wrapper = mount(GridFields, {
        props: { handle: 'fields', value: [], config: {} },
        global: {
            provide: {
                [publishContextKey]: {},
                fieldtypeContext: { blueprint: 'runway::post', field_path: ['rows'], nested: false },
            },
            stubs: { Fields: Probe },
        },
    });
    expect(JSON.parse(wrapper.find('output').text())).toEqual({
        blueprint: 'runway::post', field_path: ['rows'], nested: true,
    });
    wrapper.unmount();
});
