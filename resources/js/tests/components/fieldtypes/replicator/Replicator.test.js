import { mount } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import Replicator from '@/components/fieldtypes/replicator/Replicator.vue';
import { containerContextKey } from '@/components/ui/Publish/Container.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;
window.__n = (key, count) => key.split('|')[count === 1 ? 0 : 1].replace(/:count/g, count);

const sets = [
    { _id: 'set-1', type: 'card', enabled: true },
    { _id: 'set-2', type: 'card', enabled: true },
];

function mountField(collapsed) {
    return mount(Replicator, {
        shallow: true,
        props: {
            value: sets,
            handle: 'blocks',
            config: { collapse: true, sets: [{ handle: 'main', sets: [{ handle: 'card', fields: [] }] }] },
            meta: { existing: { 'set-1': {}, 'set-2': {} }, collapsed, defaults: {}, new: {} },
        },
        global: {
            mocks: {
                $keys: { bindGlobal: () => ({ destroy: () => {} }) },
                $progress: { loading: () => {} },
            },
            provide: {
                [containerContextKey]: {
                    values: { value: {} },
                    errors: { value: {} },
                    setFieldValue: vi.fn(),
                    setFieldMeta: vi.fn(),
                },
            },
        },
    });
}

function lastMeta(wrapper) {
    return wrapper.emitted('update:meta').at(-1)[0];
}

beforeEach(() => {
    window.Statamic = { $fieldActions: { get: () => [] } };
});

test('expanding a set persists the collapsed state to meta', async () => {
    const wrapper = mountField(['set-1', 'set-2']);

    wrapper.vm.expandSet('set-1');
    await wrapper.vm.$nextTick();

    expect(lastMeta(wrapper).collapsed).toEqual(['set-2']);
});

test('collapsing a set persists the collapsed state to meta', async () => {
    const wrapper = mountField([]);

    wrapper.vm.collapseSet('set-2');
    await wrapper.vm.$nextTick();

    expect(lastMeta(wrapper).collapsed).toEqual(['set-2']);
});
