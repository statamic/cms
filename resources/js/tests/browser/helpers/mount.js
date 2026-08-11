import { mount, flushPromises } from '@vue/test-utils';
import { h, nextTick, defineComponent, ref } from 'vue';
import wait from '@/util/wait.js';
import Container, { containerContextKey } from '@/components/ui/Publish/Container.vue';
import Fields from '@/components/ui/Publish/Fields.vue';
import FieldsProvider from '@/components/ui/Publish/FieldsProvider.vue';
import BardFieldtype from '@/components/fieldtypes/bard/BardFieldtype.vue';
import ReplicatorFieldtype from '@/components/fieldtypes/replicator/Replicator.vue';
import TextFieldtype from '@/components/fieldtypes/TextFieldtype.vue';
import { Input } from '@/components/ui';
import {
    makeBardConfig,
    makeBardMeta,
    makeBardValueFromProfile,
    makeReplicatorValueFromProfile,
} from '../fixtures/index.js';
import perf from '@/util/perf.js';

function registerFieldtypes() {
    Statamic.$components.register('text-fieldtype', TextFieldtype);
    Statamic.$components.register('bard-fieldtype', BardFieldtype);
    Statamic.$components.register('replicator-fieldtype', ReplicatorFieldtype);
}

function makePublishContainerStub({ values = {}, meta = {} } = {}) {
    const valuesRef = ref(values);
    const metaRef = ref(meta);

    return {
        name: 'bench',
        reference: null,
        blueprint: { handle: 'bench', token: 'bench-token' },
        values: valuesRef,
        meta: metaRef,
        site: 'default',
        errors: {},
        readOnly: false,
        previews: ref({}),
        setFieldValue: (path, value) => data_set(valuesRef.value, path, value),
        setFieldMeta: (path, value) => data_set(metaRef.value, path, value),
        setFieldPreviewValue: () => {},
        syncField: () => {},
        desyncField: () => {},
    };
}

export async function mountPublishForm({ fields, values, meta = {}, blueprint } = {}) {
    registerFieldtypes();

    const wrapper = mount(Container, {
        props: {
            blueprint: blueprint || { handle: 'bench', tabs: [{ handle: 'main', sections: [{ fields }] }] },
            modelValue: values,
            meta,
            site: 'default',
            trackDirtyState: true,
        },
        global: {
            components: {
                'text-fieldtype': TextFieldtype,
                'bard-fieldtype': BardFieldtype,
                'replicator-fieldtype': ReplicatorFieldtype,
                Input,
            },
            stubs: {
                portal: {
                    template: '<div><slot /></div>',
                },
            },
        },
        slots: {
            default: () =>
                h(FieldsProvider, { fields }, () => h(Fields)),
        },
    });

    await flushPromises();
    await nextTick();

    return wrapper;
}

export async function mountBard({ profile = 'small', withSets = false, overrides = {} } = {}) {
    registerFieldtypes();

    // TipTap set node-views need a fully wired Vue app provide tree.
    // Default benches use paragraphs-only docs; pass withSets/overrides explicitly for set scenarios.
    const profileOverrides = withSets
        ? overrides
        : { ...overrides, sets: 0, nestingDepth: 0 };

    const value = makeBardValueFromProfile(profile, profileOverrides);
    const config = makeBardConfig({ withSets });
    const meta = makeBardMeta(value);
    const container = makePublishContainerStub({
        values: { content: value },
        meta: { content: meta },
    });

    const wrapper = mount(BardFieldtype, {
        props: {
            handle: 'content',
            value,
            config,
            meta,
        },
        global: {
            components: {
                'text-fieldtype': TextFieldtype,
                'bard-fieldtype': BardFieldtype,
                Input,
            },
            stubs: {
                portal: {
                    template: '<div><slot /></div>',
                },
                'publish-field-fullscreen-header': true,
                'ui-button': true,
                'ui-description': true,
            },
            provide: {
                [containerContextKey]: container,
            },
        },
    });

    // Bard's mounted() is async (dynamic tipTap import).
    await waitFor(() => wrapper.vm.editor, {
        timeout: 15000,
        message: 'Bard editor failed to initialize',
    });

    return { wrapper, value, config, meta, container };
}

async function waitFor(getter, { timeout = 5000, message = 'Timed out waiting for condition' } = {}) {
    const started = Date.now();

    while (Date.now() - started < timeout) {
        await flushPromises();
        await nextTick();

        const value = getter();
        if (value) return value;

        await wait(25);
    }

    throw new Error(message);
}

export async function mountReplicator({ profile = 'small', overrides = {}, shallow = false } = {}) {
    registerFieldtypes();

    const { value, config, meta } = makeReplicatorValueFromProfile(profile, overrides);
    const container = makePublishContainerStub({
        values: { blocks: value },
        meta: { blocks: meta },
    });

    const wrapper = mount(ReplicatorFieldtype, {
        shallow,
        props: {
            handle: 'blocks',
            value,
            config,
            meta,
            id: 'blocks-field',
        },
        global: {
            components: {
                'text-fieldtype': TextFieldtype,
                'replicator-fieldtype': ReplicatorFieldtype,
                Input,
            },
            stubs: {
                portal: {
                    template: '<div><slot /></div>',
                },
                'publish-field-fullscreen-header': true,
                ReplicatorSet: true,
                'sortable-list': true,
                'add-set-button': true,
            },
            provide: {
                [containerContextKey]: container,
            },
        },
    });

    await flushPromises();
    await nextTick();

    if (!wrapper.vm?.value) {
        throw new Error('Replicator failed to mount');
    }

    return { wrapper, value, config, meta, container };
}

export async function mountPublishWithBard(profile = 'medium') {
    // Paragraphs-only for stable publish-form mounts; set node-views need fuller app wiring.
    const value = makeBardValueFromProfile(profile, { sets: 0, nestingDepth: 0 });
    const config = { handle: 'content', ...makeBardConfig({ withSets: false }) };
    const meta = { content: makeBardMeta(value) };

    return mountPublishForm({
        fields: [config],
        values: { content: value },
        meta,
    });
}

export async function mountPublishWithReplicator(profile = 'medium') {
    const { value, config, meta } = makeReplicatorValueFromProfile(profile);

    return mountPublishForm({
        fields: [{ handle: 'blocks', ...config }],
        values: { blocks: value },
        meta: { blocks: meta },
    });
}

/**
 * Time an async/sync operation once using the shared perf module.
 * Useful inside benches that care about a single interaction latency
 * rather than tinybench's ops/sec loop.
 */
export async function timeOnce(name, fn) {
    perf.reset();
    perf.enable();
    perf.start(name);

    const result = await fn();

    perf.stop(name);

    return {
        result,
        report: perf.reportJson(),
        duration: perf.reportJson().find((row) => row.name === name)?.mean ?? 0,
    };
}

export function Probe(callback) {
    return defineComponent({
        setup() {
            callback();
            return () => h('div');
        },
    });
}
