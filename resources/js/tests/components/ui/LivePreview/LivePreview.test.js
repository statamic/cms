import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { ref } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import axios from 'axios';
import LivePreview from '@/components/ui/LivePreview/LivePreview.vue';

vi.mock('axios', () => ({
    default: { post: vi.fn() },
}));

vi.mock('@/components/ui/LivePreview/ManagesIframes.js', async () => {
    const { ref } = await import('vue');
    return { useIframeManager: () => ({ previousUrl: ref(null), updateIframeContents: vi.fn() }) };
});

vi.mock('@ui', async () => {
    const { defineComponent, inject } = await import('vue');
    const stub = defineComponent({ setup: (props, { slots }) => () => slots.default?.() });

    return {
        Select: stub,
        Button: stub,
        injectPublishContext: () => inject('PublishContainerContext', null),
    };
});

let values;
let events;

function livePreview(props = {}) {
    return mount(LivePreview, {
        props: { enabled: false, url: '/preview', targets: [{ label: 'Default' }], ...props },
        global: {
            provide: {
                PublishContainerContext: {
                    name: ref('entry-form'),
                    blueprint: ref({ handle: 'article' }),
                    values,
                },
            },
            stubs: { 'v-portal': true, portal: true, 'portal-target': true, Resizer: true },
        },
    });
}

// The debounce is configured to 0ms below, but still goes through a timer.
async function settle() {
    await vi.advanceTimersByTimeAsync(1);
    await flushPromises();
}

async function enable(wrapper) {
    await wrapper.setProps({ enabled: true });
    await settle();
}

beforeEach(() => {
    vi.useFakeTimers();

    values = ref({ title: 'One' });

    events = {
        handlers: {},
        $on(event, handler) {
            (this.handlers[event] ??= []).push(handler);
        },
        $off(event, handler) {
            this.handlers[event] = (this.handlers[event] ?? []).filter((h) => h !== handler);
        },
        $emit(event) {
            (this.handlers[event] ?? []).forEach((handler) => handler());
        },
    };

    global.__ = (key) => key;

    global.Statamic = {
        $config: {
            get: (key, fallback) =>
                ({
                    'livePreview.debounce_ms': 0,
                    'livePreview.devices': { Responsive: {} },
                    'livePreview.inputs': {},
                })[key] ?? fallback,
        },
        $keys: { bindGlobal: () => ({ destroy: vi.fn() }) },
        $events: events,
    };

    axios.post.mockResolvedValue({ data: { token: 'token', url: '/preview/rendered' } });
});

afterEach(() => {
    vi.useRealTimers();
    vi.clearAllMocks();
});

test('it does not post while disabled, even when the values change', async () => {
    livePreview();

    values.value.title = 'Two';
    await settle();

    expect(axios.post).not.toHaveBeenCalled();
});

test('it posts once when enabled', async () => {
    await enable(livePreview());

    expect(axios.post).toHaveBeenCalledTimes(1);
    expect(axios.post.mock.calls[0][1]).toEqual({
        blueprint: 'article',
        preview: { title: 'One' },
        extras: {},
    });
});

test('it posts when the values change while enabled', async () => {
    await enable(livePreview());

    values.value.title = 'Two';
    await settle();

    expect(axios.post).toHaveBeenCalledTimes(2);
    expect(axios.post.mock.calls[1][1].preview).toEqual({ title: 'Two' });
});

test('it does not post again when the values change into an identical payload', async () => {
    await enable(livePreview());

    // A new object, but one that serializes to the payload we just posted.
    values.value = { title: 'One' };
    await settle();

    expect(axios.post).toHaveBeenCalledTimes(1);
});

test('it posts on an explicit refresh even when the payload is unchanged', async () => {
    await enable(livePreview());

    events.$emit('live-preview.entry-form.refresh');
    await settle();

    expect(axios.post).toHaveBeenCalledTimes(2);
});

test('it stops posting once disabled', async () => {
    const wrapper = livePreview();
    await enable(wrapper);

    await wrapper.setProps({ enabled: false });
    await settle();

    values.value.title = 'Two';
    await settle();

    expect(axios.post).toHaveBeenCalledTimes(1);
});

test('it does not read the values tree at all while disabled', async () => {
    let reads = 0;
    values = ref({
        title: 'One',
        get watched() {
            reads++;
            return 'anything';
        },
    });

    livePreview();
    await settle();

    const before = reads;
    values.value.title = 'Two';
    await settle();

    // Nothing should be deep-watching the tree, so the change goes unread.
    expect(reads).toBe(before);
});

test('it aborts an in-flight request when disabled', async () => {
    let signal;
    axios.post.mockImplementation((url, body, config) => {
        signal = config.signal;
        return new Promise(() => {});
    });

    const wrapper = livePreview();
    await enable(wrapper);

    expect(signal.aborted).toBe(false);

    await wrapper.setProps({ enabled: false });
    await settle();

    expect(signal.aborted).toBe(true);
});

test('it watches the payload when mounted already enabled', async () => {
    livePreview({ enabled: true });

    values.value.title = 'Two';
    await settle();

    expect(axios.post).toHaveBeenCalledTimes(1);
    expect(axios.post.mock.calls[0][1].preview).toEqual({ title: 'Two' });
});
