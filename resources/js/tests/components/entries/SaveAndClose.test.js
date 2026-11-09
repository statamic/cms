import { flushPromises, shallowMount } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';
import { defineComponent, h, reactive } from 'vue';
import PublishForm from '@/components/entries/PublishForm.vue';

let pipelineResult;

vi.mock('@ui/Publish/SavePipeline.js', () => {
    class Step {}
    class PipelineStopped extends Error {}
    class Pipeline {
        provide() {
            return this;
        }
        through() {
            return this;
        }
        then(callback) {
            return pipelineResult.then(callback);
        }
    }

    return { Pipeline, PipelineStopped, Request: Step, BeforeSaveHooks: Step, AfterSaveHooks: Step };
});

window.__ = (key) => key;
window.Statamic = { $commandPalette: { add: () => {}, category: {}, remove: () => {} } };

const ContainerStub = defineComponent({
    methods: {
        saved() {},
        setFieldValue() {},
    },
    render: () => h('div'),
});

const response = { data: { data: { title: 'Ada Lovelace', published: true, status: 'published' } } };

let bindings;
const progress = reactive({ complete: true });

function mountForm(props = {}) {
    return shallowMount(PublishForm, {
        props: {
            publishContainer: 'base',
            initialFieldset: { handle: 'speaker', tabs: [] },
            initialValues: { title: 'Ada Lovelace' },
            initialMeta: {},
            initialLocalizations: [{ handle: 'default', active: true }],
            collectionHandle: 'speakers',
            initialActions: { save: '/save' },
            method: 'patch',
            ...props,
        },
        global: {
            stubs: { PublishContainer: ContainerStub },
            mocks: {
                $progress: { isComplete: () => progress.complete, loading: () => {} },
                $config: { get: () => 'ltr' },
                $preferences: { get: () => null },
                $toast: { success: () => {}, error: () => {} },
                $keys: {
                    bindGlobal: (keys, callback) => {
                        keys.forEach((key) => (bindings[key] = callback));
                        return { destroy: () => {} };
                    },
                },
                $events: { $on: () => {}, $off: () => {}, $emit: () => {} },
            },
        },
    });
}

function press(key) {
    bindings[key]({ preventDefault: () => {} });
}

beforeEach(() => {
    bindings = {};
    progress.complete = true;
    pipelineResult = Promise.resolve(response);
});

test('the save and close shortcut is only bound for inline forms', () => {
    mountForm();
    expect(bindings).not.toHaveProperty('mod+shift+s');

    mountForm({ isInline: true });
    expect(bindings).toHaveProperty('mod+shift+s');
});

test('saving with the shortcut emits saved and then close', async () => {
    const wrapper = mountForm({ isInline: true });

    press('mod+shift+s');
    await flushPromises();

    expect(wrapper.emitted('saved')).toHaveLength(1);
    expect(wrapper.emitted('close')).toHaveLength(1);
    expect(Object.keys(wrapper.emitted()).filter((e) => ['saved', 'close'].includes(e))).toEqual(['saved', 'close']);
    expect(wrapper.vm.closeAfterSave).toBe(false);
});

test('a failed save does not close', async () => {
    pipelineResult = Promise.reject(new Error('Nope'));
    vi.spyOn(console, 'error').mockImplementation(() => {});
    const wrapper = mountForm({ isInline: true });

    press('mod+shift+s');
    await flushPromises();

    expect(wrapper.emitted('close')).toBeUndefined();
    expect(wrapper.vm.closeAfterSave).toBe(false);
});

test('a quick save does not close', async () => {
    const wrapper = mountForm({ isInline: true });

    press('mod+s');
    await flushPromises();

    expect(wrapper.emitted('saved')).toHaveLength(1);
    expect(wrapper.emitted('close')).toBeUndefined();
});

test('the shortcut is ignored while something is loading', async () => {
    progress.complete = false;
    const wrapper = mountForm({ isInline: true });

    press('mod+shift+s');
    await flushPromises();

    expect(wrapper.vm.closeAfterSave).toBe(false);
    expect(wrapper.emitted('close')).toBeUndefined();
});

test('another save during the request does not cancel the close', async () => {
    let resolve;
    pipelineResult = new Promise((r) => (resolve = r));
    const wrapper = mountForm({ isInline: true });

    press('mod+shift+s');
    progress.complete = false;
    press('mod+s');
    progress.complete = true;
    resolve(response);
    await flushPromises();

    expect(wrapper.emitted('close')).toHaveLength(1);
});
