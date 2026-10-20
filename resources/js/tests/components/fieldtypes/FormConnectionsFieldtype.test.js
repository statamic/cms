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
                Stack: defineComponent({
                    name: 'Stack',
                    props: ['open', 'beforeClose', 'title', 'size'],
                    emits: ['update:open'],
                    setup: (_, { slots }) => () => h('div', [slots.default?.(), slots['footer-end']?.()]),
                }),
                ConnectorsListing: defineComponent({ name: 'ConnectorsListing', emits: ['select'], render: () => null }),
                ConnectionEditor: defineComponent({ name: 'ConnectionEditor', props: ['component', 'modelValue', 'errors'], render: () => null }),
                Button: defineComponent({ name: 'Button', props: ['text', 'disabled', 'loading'], emits: ['click'], render: () => null }),
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

const apply = (wrapper) =>
    wrapper
        .findAllComponents({ name: 'Button' })
        .find((button) => button.props('text') === 'Apply')
        .vm.$emit('click');

const editorStack = (wrapper) => wrapper.findAllComponents({ name: 'Stack' })[1];

const openEditor = async (wrapper, handle, value = []) => {
    axios.post.mockResolvedValueOnce(editorResponse(`${handle}-connector`, value));
    open(wrapper, handle);
    await flushPromises();
};

const pendingApply = async (wrapper) => {
    const validate = deferred();
    axios.post.mockReturnValueOnce(validate.promise);
    apply(wrapper);
    await flushPromises();

    return validate;
};

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

test('apply does nothing while the editor is loading', async () => {
    axios.post.mockReturnValueOnce(new Promise(() => {}));

    const wrapper = mountFieldtype();

    open(wrapper, 'email');
    apply(wrapper);
    await flushPromises();

    expect(axios.post).toHaveBeenCalledTimes(1);
    expect(axios.post).toHaveBeenCalledWith('/cp/forms/contact/connect/email/editor', expect.anything());
});

test('reopening a connector after applying fetches its editor using the applied value', async () => {
    const wrapper = mountFieldtype();
    await openEditor(wrapper, 'email');

    const applied = [{ id: 'applied', to: ['applied@example.com'] }];
    axios.post.mockResolvedValueOnce({ data: applied });
    apply(wrapper);
    await flushPromises();

    const value = wrapper.emitted('update:value').at(-1)[0];
    expect(value).toEqual({ email: applied });
    await wrapper.setProps({ value });

    await openEditor(wrapper, 'email');

    expect(axios.post).toHaveBeenLastCalledWith('/cp/forms/contact/connect/email/editor', applied);
});

test('the editor cannot be closed while applying', async () => {
    const wrapper = mountFieldtype();
    await openEditor(wrapper, 'email');

    expect(editorStack(wrapper).props('beforeClose')()).toBe(true);

    const validate = await pendingApply(wrapper);

    expect(editorStack(wrapper).props('beforeClose')()).toBe(false);

    validate.resolve({ data: [] });
    await flushPromises();
});

test('a pending apply is discarded when the editor is closed', async () => {
    const wrapper = mountFieldtype();
    await openEditor(wrapper, 'email');
    const validate = await pendingApply(wrapper);

    editorStack(wrapper).vm.$emit('update:open', false);
    await flushPromises();

    expect(editorStack(wrapper).props('beforeClose')()).toBe(true);

    validate.resolve({ data: [{ id: 'late' }] });
    await flushPromises();

    expect(wrapper.emitted('update:value')).toBeUndefined();
});

test('a pending apply never writes to another connector', async () => {
    const wrapper = mountFieldtype();
    await openEditor(wrapper, 'email');
    const validate = await pendingApply(wrapper);

    editorStack(wrapper).vm.$emit('update:open', false);
    await openEditor(wrapper, 'webhook');

    validate.resolve({ data: [{ id: 'late' }] });
    await flushPromises();

    expect(wrapper.emitted('update:value')).toBeUndefined();
    expect(editorStack(wrapper).props('open')).toBe(true);
    expect(wrapper.findComponent({ name: 'ConnectionEditor' }).props('component').name).toBe('webhook-connector');
});

test('a stale validation failure is not shown on another connector', async () => {
    const wrapper = mountFieldtype();
    await openEditor(wrapper, 'email');
    const validate = deferred();
    axios.post.mockReturnValueOnce(validate.promise.then(() => Promise.reject({ response: { status: 422, data: { errors: { to: ['Required'] } } } })));
    apply(wrapper);
    await flushPromises();

    editorStack(wrapper).vm.$emit('update:open', false);
    await openEditor(wrapper, 'webhook');

    validate.resolve();
    await flushPromises();

    expect(wrapper.findComponent({ name: 'ConnectionEditor' }).props('errors')).toEqual({});
});
