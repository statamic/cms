import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import { defineComponent, h, nextTick, ref } from 'vue';
import * as ui from '@ui';
import * as Globals from '@/bootstrap/globals';
import TextFieldtype from '@/components/fieldtypes/TextFieldtype.vue';
import GroupFieldtype from '@/components/fieldtypes/GroupFieldtype.vue';
import Container, { injectContainerContext } from '@/components/ui/Publish/Container.vue';
import Set from '@/components/fieldtypes/replicator/Set.vue';
import { createMountScheduler } from '@/util/createMountScheduler.js';
import { Pipeline } from '@/components/ui/Publish/SavePipeline.js';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;

window.Statamic = {
    $app: {
        component: (name) => {
            if (name === 'text-fieldtype') return TextFieldtype;
            if (name === 'group-fieldtype') return GroupFieldtype;
        },
    },
    $config: {
        get: (key) => (key === 'sites' ? [{ handle: 'default', direction: 'ltr' }] : undefined),
    },
    $dirty: { has: () => false, add: () => {}, remove: () => {} },
    $events: { $emit: () => {} },
};

// Mirrors bootstrap/ui.js, which registers every `@ui` export globally as `ui-*`.
const uiComponents = Object.fromEntries(
    Object.entries(ui).map(([name, component]) => [
        `ui-${name.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase()}`,
        component,
    ]),
);

// The condition lives on a field nested inside the set's group, which is the case the
// set's headless watcher deliberately doesn't handle — nothing evaluates it unless the
// set body actually mounts. So this set is force-mounted through the mount scheduler.
const setConfig = {
    handle: 'main',
    display: 'Main',
    fields: [
        { handle: 'always', type: 'text' },
        {
            handle: 'grp',
            type: 'group',
            fields: [{ handle: 'nested_conditioned', type: 'text', if: { '$root.driver': 'equals yes' } }],
        },
    ],
};

function initialValues() {
    return {
        driver: 'no',
        rep: [
            {
                id: 'set-1',
                type: 'main',
                enabled: true,
                always: 'A',
                grp: { nested_conditioned: 'N' },
            },
        ],
    };
}

const SetHost = defineComponent({
    props: { collapsed: Boolean },
    setup(props) {
        const { values } = injectContainerContext();

        return () =>
            h(Set, {
                config: setConfig,
                id: 'set-1',
                fieldPath: 'rep',
                metaPath: 'rep',
                index: 0,
                collapsed: props.collapsed,
                values: values.value.rep[0],
                enabled: true,
            });
    },
});

function mountSet(collapsed = true) {
    return mount(Container, {
        props: {
            blueprint: { tabs: [] },
            modelValue: initialValues(),
            site: 'default',
        },
        global: {
            components: {
                ...uiComponents,
                'text-fieldtype': TextFieldtype,
                'group-fieldtype': GroupFieldtype,
            },
            provide: {
                replicatorSets: [],
                // The real scheduler, not the synchronous stub the other payload tests
                // use — the window this is about is the one before it has drained.
                mountScheduler: createMountScheduler(),
            },
            stubs: {
                'confirmation-modal': true,
                portal: { template: '<div><slot /></div>' },
                'element-container': { template: '<div><slot /></div>' },
                'publish-field-fullscreen-header': true,
            },
        },
        slots: {
            default: () => h(SetHost, { collapsed }),
        },
    });
}

function save(wrapper) {
    return new Pipeline()
        .provide({ container: ref(wrapper.vm), errors: ref({}), saving: ref(false) })
        .through([]);
}

async function settle() {
    for (let i = 0; i < 5; i++) await nextTick();
}

function clone(values) {
    return JSON.parse(JSON.stringify(values));
}

describe('saving before deferred set bodies have mounted', () => {
    beforeEach(() => {
        // The scheduler drains on idle. Browsers throttle or withhold both of these in a
        // background tab, which is exactly the "switch back and hit save" case — so hold
        // them open for the whole test and let only the save path drain the queue.
        vi.stubGlobal('requestIdleCallback', () => {});
        vi.stubGlobal('requestAnimationFrame', () => 0);
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    test('the set body has not mounted yet', async () => {
        const wrapper = mountSet();
        await settle();

        expect(wrapper.find('[data-set-body]').exists()).toBe(false);

        wrapper.unmount();
    });

    test('the save pipeline waits for it, so the payload is the same either way', async () => {
        const collapsed = mountSet(true);
        const expanded = mountSet(false);
        await settle();

        // Nothing has evaluated the nested condition yet, so the value is still there.
        expect(clone(collapsed.vm.visibleValues).rep[0].grp).toEqual({ nested_conditioned: 'N' });

        await save(collapsed);
        await save(expanded);

        expect(collapsed.find('[data-set-body]').exists()).toBe(true);
        expect(clone(collapsed.vm.visibleValues)).toEqual(clone(expanded.vm.visibleValues));

        // Pins what that payload actually is, so the equality above can't pass by both
        // sides being equally wrong.
        expect(clone(collapsed.vm.visibleValues).rep[0]).toEqual({
            id: 'set-1',
            type: 'main',
            enabled: true,
            always: 'A',
            grp: {},
        });

        collapsed.unmount();
        expanded.unmount();
    });

    test('the pipeline still resolves when a scheduled mount throws', async () => {
        const wrapper = mountSet();
        await settle();

        const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {});
        const scheduler = createMountScheduler();
        scheduler.schedule(() => { throw new Error('nope'); });

        await expect(save(wrapper)).resolves.toBeUndefined();

        consoleError.mockRestore();
        wrapper.unmount();
    });
});
