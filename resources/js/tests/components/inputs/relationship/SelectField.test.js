import { mount, flushPromises } from '@vue/test-utils';
import { describe, expect, test, vi, beforeEach } from 'vitest';

vi.mock('@inertiajs/vue3', () => ({
    router: { on: () => () => {} },
}));

globalThis.__ = (key) => key;

import { data_get } from '@/bootstrap/globals.js';
globalThis.data_get = data_get;

import SelectField from '@/components/inputs/relationship/SelectField.vue';

const stubs = {
    Combobox: true,
    StatusIndicator: true,
};

function deferred() {
    let resolve, reject;
    const promise = new Promise((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
}

function mountSelectField({ items = [], config = {}, axiosGet, url = '/test/select-field', site } = {}) {
    return mount(SelectField, {
        props: {
            items,
            url,
            config,
            site,
        },
        global: {
            mocks: {
                $axios: {
                    get: axiosGet || (() => Promise.resolve({ data: { data: [] } })),
                },
            },
            stubs,
        },
    });
}

describe('SelectField comboboxOptions', () => {
    test('includes a selected item missing from the fetched options list', () => {
        const wrapper = mountSelectField({ items: [{ id: 'tags::bob', title: 'Bob' }] });

        expect(wrapper.vm.comboboxOptions).toContainEqual({ id: 'tags::bob', title: 'Bob' });

        wrapper.unmount();
    });

    test('does not duplicate an item already present in the fetched options list', async () => {
        const wrapper = mountSelectField({ items: [{ id: '1', title: 'One' }] });
        await wrapper.setData({ options: [{ id: '1', title: 'One' }] });

        expect(wrapper.vm.comboboxOptions).toEqual([{ id: '1', title: 'One' }]);

        wrapper.unmount();
    });
});

describe('SelectField searchKeys', () => {
    test('searches by title and email for the users fieldtype', () => {
        const wrapper = mountSelectField({ config: { type: 'users' } });

        expect(wrapper.vm.searchKeys).toEqual(['title', 'email']);

        wrapper.unmount();
    });

    test('is null for other relationship fieldtypes', () => {
        const wrapper = mountSelectField({ config: { type: 'entries' } });

        expect(wrapper.vm.searchKeys).toBeNull();

        wrapper.unmount();
    });
});

describe('SelectField options request deduplication', () => {
    beforeEach(() => {
        // Force a unique url per test so module-level caches from prior tests don't interfere.
    });

    test('shares a single in-flight request across instances with the same cache key', async () => {
        const d = deferred();
        const get = vi.fn(() => d.promise);
        const url = '/test/select-inflight-' + Math.random();

        const a = mountSelectField({ axiosGet: get, url });
        const b = mountSelectField({ axiosGet: get, url });

        await flushPromises();
        expect(get).toHaveBeenCalledTimes(1);

        d.resolve({ data: { data: [{ id: '1', title: 'One' }] } });
        await flushPromises();

        expect(a.vm.options).toEqual([{ id: '1', title: 'One' }]);
        expect(b.vm.options).toEqual([{ id: '1', title: 'One' }]);

        a.unmount();
        b.unmount();
    });

    test('reuses settled options for a later instance without a second request', async () => {
        const d = deferred();
        const get = vi.fn(() => d.promise);
        const url = '/test/select-settled-' + Math.random();

        const a = mountSelectField({ axiosGet: get, url });
        await flushPromises();
        expect(get).toHaveBeenCalledTimes(1);

        d.resolve({ data: { data: [{ id: '1', title: 'One' }] } });
        await flushPromises();

        const b = mountSelectField({ axiosGet: get, url });
        await flushPromises();

        expect(get).toHaveBeenCalledTimes(1);
        expect(b.vm.options).toEqual([{ id: '1', title: 'One' }]);
        expect(b.vm.requested).toBe(true);

        a.unmount();
        b.unmount();
    });

    test('does not re-request when the parent re-renders without a site/url change', async () => {
        const get = vi.fn(() => Promise.resolve({ data: { data: [{ id: '1', title: 'One' }] } }));
        const url = '/test/select-rerender-' + Math.random();

        const wrapper = mountSelectField({ axiosGet: get, url, site: 'default' });
        await flushPromises();
        expect(get).toHaveBeenCalledTimes(1);

        // Re-evaluate the parameters computed (fresh object identity) without changing cacheKey.
        await wrapper.vm.$forceUpdate();
        await flushPromises();

        expect(get).toHaveBeenCalledTimes(1);

        wrapper.unmount();
    });
});
