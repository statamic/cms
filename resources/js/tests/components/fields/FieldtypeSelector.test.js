import { mount, flushPromises } from '@vue/test-utils';
import { describe, expect, test, vi } from 'vitest';

globalThis.__ = (key) => key;
globalThis.cp_url = (path) => `/cp/${path}`;

import FieldtypeSelector from '@/components/fields/FieldtypeSelector.vue';

const stubs = {
    Icon: true,
    'ui-input': true,
    'ui-panel': true,
    'ui-panel-header': true,
    'ui-description': true,
    'ui-icon': true,
};

function mountSelector({ isFormBlueprint, get }) {
    return mount(FieldtypeSelector, {
        global: {
            mocks: {
                $axios: { get },
                $config: { get: (key) => (key === 'isFormBlueprint' ? isFormBlueprint : undefined) },
                $toast: { error: () => {} },
            },
            stubs,
        },
    });
}

describe('FieldtypeSelector fieldtype list caching', () => {
    // The list is cached in a module-level ref shared across every picker in the SPA session, so it
    // must be keyed on the blueprint mode or the first picker's list sticks until a page reload.
    // https://github.com/statamic/cms/issues/14903
    test('refetches the correct list when switching between regular and form blueprints without reload', async () => {
        const regular = [{ handle: 'bard', title: 'Bard', categories: ['text'], icon: 'bard', config: [] }];
        const forms = [{ handle: 'text', title: 'Text', categories: ['text'], icon: 'text', config: [] }];

        const get = vi.fn((url) => Promise.resolve({ data: url.includes('forms=true') ? forms : regular }));

        // Regular blueprint picker loads first.
        const a = mountSelector({ isFormBlueprint: false, get });
        await flushPromises();
        expect(get).toHaveBeenCalledTimes(1);
        expect(get.mock.calls[0][0]).not.toContain('forms=true');

        // Navigate (no reload) to a form blueprint picker: it must refetch its own filtered list.
        const b = mountSelector({ isFormBlueprint: true, get });
        await flushPromises();
        expect(get).toHaveBeenCalledTimes(2);
        expect(get.mock.calls[1][0]).toContain('forms=true');
        expect(b.vm.fieldtypes).toEqual(forms);

        // Back on a regular blueprint: the cached list for that mode is reused, not refetched.
        const c = mountSelector({ isFormBlueprint: false, get });
        await flushPromises();
        expect(get).toHaveBeenCalledTimes(2);
        expect(c.vm.fieldtypes).toEqual(regular);

        a.unmount();
        b.unmount();
        c.unmount();
    });
});
