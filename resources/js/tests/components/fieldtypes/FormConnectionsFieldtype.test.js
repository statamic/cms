import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import axios from 'axios';
import { toast } from '@api';
import FormConnectionsFieldtype from '@/components/fieldtypes/FormConnectionsFieldtype.vue';

vi.mock('axios', () => ({ default: { post: vi.fn() } }));

vi.mock('@api', async (importOriginal) => ({
    ...(await importOriginal()),
    preferences: { get: (key, fallback) => fallback, set: vi.fn() },
    toast: { error: vi.fn() },
}));

const type = (handle) => ({
    handle,
    title: handle,
    action: `/cp/forms/contact/connect/${handle}/validate`,
    editor: `/cp/forms/contact/connect/${handle}/editor`,
});

const mountFieldtype = () =>
    mount(FormConnectionsFieldtype, {
        props: {
            handle: 'connections',
            config: { type: 'form_connections' },
            meta: { form: { handle: 'contact', title: 'Contact' }, types: [type('email'), type('webhook')] },
            value: { email: [{ id: 'entry', to: ['entry@example.com'] }] },
        },
        global: {
            stubs: {
                Stack: defineComponent({ setup: (_, { slots }) => () => h('div', slots.default?.()) }),
                ConnectorsListing: defineComponent({ name: 'ConnectorsListing', emits: ['select'], render: () => null }),
                ConnectionEditor: defineComponent({ name: 'ConnectionEditor', props: ['component', 'modelValue'], render: () => null }),
                Button: true,
                Badge: true,
                Card: defineComponent({ setup: (_, { slots }) => () => h('div', slots.default?.()) }),
                ToggleGroup: true,
                ToggleItem: true,
                Icon: true,
            },
        },
    });

const deferred = () => {
    let resolve;
    const promise = new Promise((r) => (resolve = r));

    return { promise, resolve };
};

const editorResponse = (name, value) => ({ data: { component: { name, props: {} }, value } });

const open = (wrapper, handle) => wrapper.findComponent({ name: 'ConnectorsListing' }).vm.$emit('select', handle);

beforeEach(() => vi.clearAllMocks());

test('opening a connector fetches its editor using the current value', async () => {
    axios.post.mockResolvedValue(editorResponse('email-connector', [{ id: 'entry', to: ['entry@example.com'], enabled: true }]));

    const wrapper = mountFieldtype();

    open(wrapper, 'email');
    await flushPromises();

    expect(axios.post).toHaveBeenCalledWith('/cp/forms/contact/connect/email/editor', [{ id: 'entry', to: ['entry@example.com'] }]);

    const editor = wrapper.findComponent({ name: 'ConnectionEditor' });

    expect(editor.props('component').name).toBe('email-connector');
    expect(editor.props('modelValue')).toEqual([{ id: 'entry', to: ['entry@example.com'], enabled: true }]);
});

test('a stale editor response is ignored', async () => {
    const email = deferred();
    const webhook = deferred();
    axios.post.mockReturnValueOnce(email.promise).mockReturnValueOnce(webhook.promise);

    const wrapper = mountFieldtype();

    open(wrapper, 'email');
    open(wrapper, 'webhook');

    webhook.resolve(editorResponse('webhook-connector', []));
    await flushPromises();
    email.resolve(editorResponse('email-connector', []));
    await flushPromises();

    expect(wrapper.findComponent({ name: 'ConnectionEditor' }).props('component').name).toBe('webhook-connector');
});

test('a failed editor request closes the editor', async () => {
    axios.post.mockRejectedValue(new Error('nope'));

    const wrapper = mountFieldtype();

    open(wrapper, 'email');
    await flushPromises();

    expect(toast.error).toHaveBeenCalled();
    expect(wrapper.findComponent({ name: 'ConnectionEditor' }).exists()).toBe(false);
});
