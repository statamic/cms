import { mount, flushPromises } from '@vue/test-utils';
import { describe, expect, test, vi } from 'vitest';

vi.mock('@inertiajs/vue3', () => ({
    router: { on: () => () => {} },
}));

globalThis.__ = (key) => key;

import RelationshipInput from '@/components/inputs/relationship/RelationshipInput.vue';

const stubs = {
    RelationshipSelectField: true,
    ItemSelector: true,
    CreateButton: true,
    RelatedItem: true,
    Button: true,
    Icon: true,
    Stack: true,
};

function deferred() {
    let resolve, reject;
    const promise = new Promise((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
}

function mountInput({ axiosPost, itemDataUrl, site = 'default' }) {
    return mount(RelationshipInput, {
        props: {
            value: [],
            data: [],
            config: { type: 'entries' },
            itemDataUrl,
            site,
            selectionsUrl: '/api/selections',
            filtersUrl: '/api/filters',
            mode: 'default',
        },
        global: {
            mocks: {
                $axios: { post: axiosPost },
                $progress: { loading: () => {} },
            },
            stubs,
        },
    });
}

describe('RelationshipInput in-flight request deduplication', () => {
    test('shares a single request across instances with the same selections', async () => {
        const d = deferred();
        const post = vi.fn(() => d.promise);

        const a = mountInput({ axiosPost: post, itemDataUrl: '/test/dedup-same' });
        const b = mountInput({ axiosPost: post, itemDataUrl: '/test/dedup-same' });

        a.vm.getDataForSelections(['1', '2']);
        b.vm.getDataForSelections(['1', '2']);

        await flushPromises();
        expect(post).toHaveBeenCalledTimes(1);

        d.resolve({ data: { data: [{ id: '1' }, { id: '2' }] } });
        await flushPromises();

        expect(a.emitted('item-data-updated')).toBeTruthy();
        expect(b.emitted('item-data-updated')).toBeTruthy();

        a.unmount();
        b.unmount();
    });

    test('cache key is order-insensitive across instances', async () => {
        const d = deferred();
        const post = vi.fn(() => d.promise);

        const a = mountInput({ axiosPost: post, itemDataUrl: '/test/dedup-order' });
        const b = mountInput({ axiosPost: post, itemDataUrl: '/test/dedup-order' });

        a.vm.getDataForSelections(['1', '2']);
        b.vm.getDataForSelections(['2', '1']);

        await flushPromises();
        expect(post).toHaveBeenCalledTimes(1);

        d.resolve({ data: { data: [] } });
        await flushPromises();

        a.unmount();
        b.unmount();
    });

    test('different selections each fire their own request', async () => {
        const d1 = deferred();
        const d2 = deferred();
        const post = vi.fn().mockReturnValueOnce(d1.promise).mockReturnValueOnce(d2.promise);

        const a = mountInput({ axiosPost: post, itemDataUrl: '/test/dedup-different' });
        const b = mountInput({ axiosPost: post, itemDataUrl: '/test/dedup-different' });

        a.vm.getDataForSelections(['1']);
        b.vm.getDataForSelections(['2']);

        await flushPromises();
        expect(post).toHaveBeenCalledTimes(2);

        d1.resolve({ data: { data: [] } });
        d2.resolve({ data: { data: [] } });
        await flushPromises();

        a.unmount();
        b.unmount();
    });

    test('different sites with the same selections each fire their own request', async () => {
        const d1 = deferred();
        const d2 = deferred();
        const post = vi.fn().mockReturnValueOnce(d1.promise).mockReturnValueOnce(d2.promise);

        const a = mountInput({ axiosPost: post, itemDataUrl: '/test/dedup-sites', site: 'en' });
        const b = mountInput({ axiosPost: post, itemDataUrl: '/test/dedup-sites', site: 'fr' });

        a.vm.getDataForSelections(['1']);
        b.vm.getDataForSelections(['1']);

        await flushPromises();
        expect(post).toHaveBeenCalledTimes(2);

        d1.resolve({ data: { data: [] } });
        d2.resolve({ data: { data: [] } });
        await flushPromises();

        a.unmount();
        b.unmount();
    });

    test('cache entry clears after settle so a later identical request fires fresh', async () => {
        const d1 = deferred();
        const d2 = deferred();
        const post = vi.fn().mockReturnValueOnce(d1.promise).mockReturnValueOnce(d2.promise);

        const a = mountInput({ axiosPost: post, itemDataUrl: '/test/dedup-cleanup' });
        a.vm.getDataForSelections(['1']);
        await flushPromises();
        expect(post).toHaveBeenCalledTimes(1);

        d1.resolve({ data: { data: [] } });
        await flushPromises();

        const b = mountInput({ axiosPost: post, itemDataUrl: '/test/dedup-cleanup' });
        b.vm.getDataForSelections(['1']);
        await flushPromises();
        expect(post).toHaveBeenCalledTimes(2);

        d2.resolve({ data: { data: [] } });
        await flushPromises();

        a.unmount();
        b.unmount();
    });
});
