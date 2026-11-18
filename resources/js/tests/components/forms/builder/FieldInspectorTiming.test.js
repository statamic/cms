import { mount, flushPromises } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { ref } from 'vue';
import axios from 'axios';
import FieldInspector from '@/components/forms/builder/FieldInspector.vue';

vi.mock('axios');
vi.mock('@ui', async (importOriginal) => {
    const passthrough = { template: '<div><slot /></div>' };
    return { ...(await importOriginal()), PublishContainer: passthrough, PublishFieldsProvider: passthrough, PublishFields: passthrough };
});
vi.mock('@inertiajs/vue3', async (importOriginal) => ({ ...(await importOriginal()), usePage: () => ({ props: { fieldsets: {} } }) }));

const context = {};
vi.mock('@/pages/forms/Builder.vue', () => ({ injectBuilderContext: () => context }));

globalThis.__ = (key) => key;
globalThis.cp_url = (url) => url;

let wrapper;

beforeEach(() => vi.useFakeTimers());

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    vi.useRealTimers();
});

const deferred = () => {
    let resolve;
    const promise = new Promise((r) => (resolve = r));
    return { promise, resolve };
};

function setup(field) {
    const inspecting = ref(field);
    const requests = [];

    Object.assign(context, {
        dirty: vi.fn(),
        errors: ref({}),
        fieldtypes: [{ handle: 'text', categories: ['text'] }],
        form: { handle: 'contact' },
        inspecting,
        onBeforeSave: () => () => {},
        pages: ref([{ sections: [{ fields: [field] }] }]),
        showFieldDirection: false,
        withoutDirtying: (callback) => callback(),
    });

    axios.post.mockReset();
    axios.post.mockImplementation((url, payload) => {
        if (url.endsWith('/edit')) {
            return Promise.resolve({
                data: {
                    fieldtype: {},
                    blueprint: { tabs: [{ sections: [{ fields: [] }] }] },
                    values: { ...payload.values },
                    meta: {},
                    originValues: {},
                    originMeta: {},
                },
            });
        }

        const response = deferred();
        requests.push({ values: JSON.parse(JSON.stringify(payload.values)), respond: () => response.resolve({ data: { values: JSON.parse(JSON.stringify(payload.values)) } }) });
        return response.promise;
    });

    wrapper = mount(FieldInspector, {
        global: { stubs: { FieldValidationBuilder: true } },
    });

    return { inspecting, requests };
}

const textField = () => ({
    _id: 'phone-id',
    handle: 'phone',
    fieldtype: 'text',
    icon: 'text',
    type: 'inline',
    config: { type: 'text', display: 'Phone', width: 100, hidden: false },
});

const editThenSendUpdate = async () => {
    wrapper.vm.values.display = 'Mobile';
    await flushPromises();
    vi.advanceTimersByTime(500);
    await flushPromises();
};

test('a width change made while an update is in flight is not reverted by its response', async () => {
    const { inspecting, requests } = setup(textField());
    await flushPromises();

    await editThenSendUpdate();
    expect(requests).toHaveLength(1);

    inspecting.value.config.width = 50;
    await flushPromises();

    requests[0].respond();
    await flushPromises();

    expect(wrapper.vm.values.width).toBe(50);

    vi.advanceTimersByTime(500);
    await flushPromises();
    requests.at(-1).respond();
    await flushPromises();

    expect(requests.at(-1).values).toMatchObject({ display: 'Mobile', width: 50 });
    expect(inspecting.value.config).toMatchObject({ display: 'Mobile', width: 50 });
});

test('hiding a field while an update is in flight is not reverted by its response', async () => {
    const { inspecting, requests } = setup(textField());
    await flushPromises();

    await editThenSendUpdate();

    inspecting.value.config.hidden = true;
    await flushPromises();

    requests[0].respond();
    await flushPromises();

    expect(wrapper.vm.values.hidden).toBe(true);

    vi.advanceTimersByTime(500);
    await flushPromises();
    requests.at(-1).respond();
    await flushPromises();

    expect(inspecting.value.config).toMatchObject({ display: 'Mobile', hidden: true });
});
