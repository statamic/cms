import { flushPromises, shallowMount } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import PublishForm from '@/components/entries/PublishForm.vue';

let pipelineResult;

vi.mock('@ui/Publish/SavePipeline.js', () => {
    class Step {}
    class PipelineStopped extends Error {
        constructor(payload) {
            super();
            this.payload = payload;
        }
    }
    class Pipeline {
        provide() {
            return this;
        }
        through() {
            return this;
        }
        then(callback) {
            return pipelineResult().then(callback);
        }
    }

    return { Pipeline, PipelineStopped, Request: Step, BeforeSaveHooks: Step, AfterSaveHooks: Step };
});

const { PipelineStopped } = await import('@ui/Publish/SavePipeline.js');

window.__ = (key) => key;
window.Statamic = { $commandPalette: { add: () => {}, category: {}, remove: () => {} } };

const setFieldValue = vi.fn();

const ContainerStub = defineComponent({
    methods: { saved() {}, setFieldValue },
    render: () => h('div'),
});

const response = { data: { data: { title: 'Ada Lovelace', published: true, status: 'published' } } };

function mountForm(props = {}) {
    return shallowMount(PublishForm, {
        props: {
            publishContainer: 'base',
            initialFieldset: { handle: 'speaker', tabs: [] },
            initialValues: { title: 'Ada Lovelace', _version: 'stale' },
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
                $progress: { isComplete: () => true, loading: () => {} },
                $config: { get: () => 'ltr' },
                $preferences: { get: () => null },
                $toast: { success: () => {}, error: vi.fn() },
                $keys: { bindGlobal: () => ({ destroy: () => {} }) },
                $dirty: { has: () => true, add: () => {}, remove: () => {} },
                $events: { $on: () => {}, $off: () => {}, $emit: () => {} },
            },
        },
    });
}

beforeEach(() => {
    setFieldValue.mockClear();
});

test('a conflict opens the dialog and stops autosaving', async () => {
    pipelineResult = () => Promise.reject(new PipelineStopped({ message: 'Changed', version: 'current' }));
    const wrapper = mountForm({ autosaveInterval: 5000 });
    expect(wrapper.vm.autosaveIntervalInstance).not.toBeNull();

    wrapper.vm.save();
    await flushPromises();

    expect(wrapper.vm.conflict).toEqual({ message: 'Changed', version: 'current' });
    expect(wrapper.vm.autosaveIntervalInstance).toBeNull();
    expect(wrapper.vm.$toast.error).not.toHaveBeenCalled();
});

test('saving anyway resends with the current version', async () => {
    pipelineResult = () => Promise.reject(new PipelineStopped({ message: 'Changed', version: 'current' }));
    const wrapper = mountForm({ isInline: true });
    wrapper.vm.save();
    await flushPromises();

    pipelineResult = () => Promise.resolve(response);
    wrapper.vm.saveAnyway();
    await flushPromises();

    expect(setFieldValue).toHaveBeenCalledWith('_version', 'current');
    expect(wrapper.vm.conflict).toBeNull();
    expect(wrapper.emitted('saved')).toHaveLength(1);
});
