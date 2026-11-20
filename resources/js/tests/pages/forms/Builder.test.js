import { flushPromises, shallowMount } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';
import { ref } from 'vue';
import axios from 'axios';
import Builder from '@/pages/forms/Builder.vue';

vi.mock('axios');
vi.mock('@api', async (importOriginal) => ({
    ...(await importOriginal()),
    progress: { loading: () => {} },
    keys: { bindGlobal: () => ({ destroy: () => {} }) },
}));
vi.mock('@/composables/forms/field-numbering', () => ({ useFieldNumberingPreference: () => ({ showFieldNumbers: ref(false) }) }));
vi.mock('@inertiajs/vue3', async (importOriginal) => ({ ...(await importOriginal()), usePage: () => ({ props: { fieldsets: {} } }) }));

const dirtyState = new Set();

globalThis.Statamic = {
    $dirty: { add: (key) => dirtyState.add(key), remove: (key) => dirtyState.delete(key), has: (key) => dirtyState.has(key) },
    $toast: { success: () => {}, error: () => {} },
    $config: { get: () => null },
};

const deferred = () => {
    let resolve;
    const promise = new Promise((r) => (resolve = r));
    return { promise, resolve };
};

beforeEach(() => {
    dirtyState.clear();
    axios.patch.mockReset();
});

const mountBuilder = () =>
    shallowMount(Builder, {
        props: {
            form: { handle: 'contact', title: 'Contact' },
            initialFormFields: { pages: [{ _id: 'page', sections: [{ _id: 'section', fields: [] }] }] },
            formsProInstalled: false,
            fieldtypes: [],
            action: '/builder',
        },
        global: { stubs: { Teleport: true } },
    });

test('saving waits for the inspector to finish pending updates', async () => {
    axios.patch.mockResolvedValue({});
    const wrapper = mountBuilder();
    const pending = deferred();

    wrapper.vm.onBeforeSave(() => pending.promise);
    wrapper.vm.save();
    await flushPromises();

    expect(axios.patch).not.toHaveBeenCalled();

    pending.resolve();
    await flushPromises();

    expect(axios.patch).toHaveBeenCalledOnce();
});

test('saving clears the dirty state', async () => {
    axios.patch.mockResolvedValue({});
    const wrapper = mountBuilder();

    wrapper.vm.dirty();
    wrapper.vm.save();
    await flushPromises();

    expect(dirtyState.has('form-builder')).toBe(false);
});

test('changes made while saving keep the dirty state', async () => {
    const response = deferred();
    axios.patch.mockReturnValue(response.promise);
    const wrapper = mountBuilder();

    wrapper.vm.dirty();
    wrapper.vm.save();
    await flushPromises();

    wrapper.vm.dirty();
    response.resolve({});
    await flushPromises();

    expect(dirtyState.has('form-builder')).toBe(true);
});
