import { flushPromises, mount, shallowMount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import FieldtypeSelector from '@/components/fields/FieldtypeSelector.vue';
import Fields from '@/components/blueprints/Fields.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;
window.cp_url = (url) => url;

window.Statamic = {
    $config: { get: (key) => (key === 'sites' ? [{ handle: 'default' }] : undefined) },
    $commandPalette: { add: () => {}, category: { Actions: 'actions' } },
    $toast: { success: () => {} },
};

const fieldtypes = [
    {
        handle: 'test',
        title: 'Test',
        icon: 'test-fieldtype-icon',
        categories: ['special'],
        keywords: [],
        config: [
            { handle: 'icon', default: 'default-icon' },
            { handle: 'foo', default: 'bar' },
        ],
    },
    {
        handle: 'text',
        title: 'Text',
        icon: 'text-fieldtype-icon',
        categories: ['text'],
        keywords: [],
        config: [],
    },
];

async function mountSelector() {
    const wrapper = mount(FieldtypeSelector, {
        props: { allowTitle: true },
        global: {
            mocks: {
                $axios: { get: () => Promise.resolve({ data: fieldtypes }) },
                $config: { get: () => undefined },
                $toast: { error: () => {} },
            },
            stubs: {
                'ui-input': true,
                'ui-panel': true,
                'ui-panel-header': true,
                'ui-description': true,
                'ui-icon': true,
            },
        },
    });

    await flushPromises();

    return wrapper;
}

test('the fieldtype icon is emitted alongside the config, leaving an "icon" config field intact', async () => {
    const wrapper = await mountSelector();

    wrapper.vm.select({ value: 'test' });

    const [{ icon, config }] = wrapper.emitted('selected')[0];

    expect(icon).toBe('test-fieldtype-icon');
    expect(config.type).toBe('test');
    expect(config.icon).toBe('default-icon');
    expect(config.foo).toBe('bar');
});

test('meta fields also emit the icon alongside the config', async () => {
    const wrapper = await mountSelector();

    wrapper.vm.select({ value: 'title', isMeta: true });

    const [{ icon, config }] = wrapper.emitted('selected')[0];

    expect(icon).toBe('text-fieldtype-icon');
    expect(config.isMeta).toBe(true);
    expect(config.type).toBe('text');
});

test('the fieldtype icon does not leak into the created field config', () => {
    const wrapper = shallowMount(Fields, {
        props: { fields: [] },
        global: {
            mocks: { $toast: { success: () => {} } },
        },
    });

    wrapper.vm.fieldtypeSelected({
        icon: 'test-fieldtype-icon',
        config: { type: 'test', icon: 'default-icon' },
    });

    const pending = wrapper.vm.pendingCreatedField;

    expect(pending.icon).toBe('test-fieldtype-icon');
    expect(pending.fieldtype).toBe('test');
    expect(pending.config.icon).toBe('default-icon');
});
