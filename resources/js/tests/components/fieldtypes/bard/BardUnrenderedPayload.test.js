import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { h, nextTick } from 'vue';
import * as ui from '@ui';
import * as Globals from '@/bootstrap/globals';
import TextFieldtype from '@/components/fieldtypes/TextFieldtype.vue';
import Container from '@/components/ui/Publish/Container.vue';
import BardFieldtype from '@/components/fieldtypes/bard/BardFieldtype.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;
window.__n = (key) => key;
window.markdown = (value) => value;

// The lazy editor only initialises when the field scrolls into view. Put every element
// well below the fold and never intersect, so the field stays in the state this test is
// about: rendered, but with no editor and therefore no set node views.
Element.prototype.getBoundingClientRect = () => ({
    top: 5000,
    bottom: 5100,
    left: 0,
    right: 0,
    width: 0,
    height: 100,
    x: 0,
    y: 5000,
});

window.IntersectionObserver = class {
    observe() {}
    disconnect() {}
};

window.Statamic = {
    $app: { component: (name) => (name === 'text-fieldtype' ? TextFieldtype : undefined) },
    $components: { has: () => true, register: () => {} },
    $commandPalette: { preventIf: () => {} },
    $config: {
        get: (key) => (key === 'sites' ? [{ handle: 'default', direction: 'ltr' }] : undefined),
    },
    $dirty: { has: () => false, add: () => {}, remove: () => {} },
    $events: { $emit: () => {}, $on: () => {}, $off: () => {} },
    $keys: { bindGlobal: () => ({ destroy: () => {} }) },
    $progress: { loading: () => {} },
};

const uiComponents = Object.fromEntries(
    Object.entries(ui).map(([name, component]) => [
        `ui-${name.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase()}`,
        component,
    ]),
);

const setConfig = {
    handle: 'main',
    display: 'Main',
    fields: [
        { handle: 'always', type: 'text' },
        { handle: 'local_driver', type: 'text' },
        { handle: 'local_conditioned', type: 'text', if: { local_driver: 'equals yes' } },
        { handle: 'root_conditioned', type: 'text', if: { '$root.driver': 'equals yes' } },
    ],
};

// The set's parent is the root, so `driver` being `yes` means this condition passes and
// the field is kept — if anything could resolve it.
const parentSetConfig = {
    handle: 'main',
    display: 'Main',
    fields: [
        { handle: 'always', type: 'text' },
        { handle: 'parent_conditioned', type: 'text', if: { '$parent.driver': 'equals yes' } },
    ],
};

// A revealer only registers itself with the container when it mounts, and that
// registration is what keeps a field gated on it out of the omitted list. Nothing in this
// field ever mounts, so the registration never happens.
const revealerSetConfig = {
    handle: 'main',
    display: 'Main',
    fields: [
        { handle: 'always', type: 'text' },
        { handle: 'rev', type: 'revealer' },
        { handle: 'revealer_conditioned', type: 'text', if: { rev: 'equals true' } },
    ],
};

function initialValues() {
    return {
        driver: 'no',
        bard: [
            {
                type: 'set',
                attrs: {
                    id: 'set-1',
                    enabled: true,
                    values: {
                        type: 'main',
                        always: 'A',
                        local_driver: 'no',
                        local_conditioned: 'L',
                        root_conditioned: 'R',
                    },
                },
            },
        ],
    };
}

function parentInitialValues() {
    return {
        driver: 'yes',
        bard: [
            {
                type: 'set',
                attrs: {
                    id: 'set-1',
                    enabled: true,
                    values: {
                        type: 'main',
                        always: 'A',
                        parent_conditioned: 'P',
                    },
                },
            },
        ],
    };
}

function revealerInitialValues() {
    return {
        driver: 'no',
        bard: [
            {
                type: 'set',
                attrs: {
                    id: 'set-1',
                    enabled: true,
                    values: {
                        type: 'main',
                        always: 'A',
                        revealer_conditioned: 'V',
                    },
                },
            },
        ],
    };
}

function mountBard(set = setConfig, values = initialValues()) {
    return mount(Container, {
        props: {
            blueprint: { tabs: [] },
            modelValue: values,
            site: 'default',
        },
        global: {
            components: { ...uiComponents, 'text-fieldtype': TextFieldtype },
            mocks: { $bard: { buttonCallbacks: [] } },
            stubs: {
                portal: { template: '<div><slot /></div>' },
                'publish-field-fullscreen-header': true,
            },
        },
        slots: {
            default: () =>
                h(BardFieldtype, {
                    handle: 'bard',
                    value: values.bard,
                    meta: { collapsed: [], existing: { 'set-1': {} } },
                    config: {
                        handle: 'bard',
                        type: 'bard',
                        sets: [{ handle: 'group', sets: [set] }],
                        buttons: [],
                    },
                }),
        },
    });
}

// A `$root.` condition in one bard pointing at a field in another. Neither is ever
// scrolled into view, so neither builds an editor.
function crossBardSets(target) {
    return {
        a: {
            handle: 'main',
            display: 'Main',
            fields: [
                { handle: 'always', type: 'text' },
                { handle: 'local_driver', type: 'text' },
                { handle: 'local_conditioned', type: 'text', if: { local_driver: 'equals yes' } },
                { handle: 'gated', type: 'text', if: { [`$root.bard_b.0.attrs.values.${target}`]: 'equals yes' } },
            ],
        },
        revealer: {
            handle: 'other',
            display: 'Other',
            fields: [{ handle: 'rev', type: 'revealer' }],
        },
        plain: {
            handle: 'other',
            display: 'Other',
            fields: [{ handle: 'flag', type: 'text' }],
        },
    };
}

function crossBardValues(otherValues) {
    return {
        bard_a: [
            {
                type: 'set',
                attrs: {
                    id: 'set-a',
                    enabled: true,
                    values: {
                        type: 'main',
                        always: 'A',
                        local_driver: 'no',
                        local_conditioned: 'L',
                        gated: 'G',
                    },
                },
            },
        ],
        bard_b: [
            {
                type: 'set',
                attrs: { id: 'set-b', enabled: true, values: { type: 'other', ...otherValues } },
            },
        ],
    };
}

function mountTwoBards(setA, setB, values) {
    const bard = (handle, set, id) =>
        h(BardFieldtype, {
            handle,
            value: values[handle],
            meta: { collapsed: [], existing: { [id]: {} } },
            config: {
                handle,
                type: 'bard',
                sets: [{ handle: 'group', sets: [set] }],
                buttons: [],
            },
        });

    return mount(Container, {
        props: {
            blueprint: { tabs: [] },
            modelValue: values,
            site: 'default',
        },
        global: {
            components: { ...uiComponents, 'text-fieldtype': TextFieldtype },
            mocks: { $bard: { buttonCallbacks: [] } },
            stubs: {
                portal: { template: '<div><slot /></div>' },
                'publish-field-fullscreen-header': true,
            },
        },
        slots: {
            default: () => [bard('bard_a', setA, 'set-a'), bard('bard_b', setB, 'set-b')],
        },
    });
}

// ShowField commits its bookkeeping inside a nextTick, and that commit can trigger
// another round of evaluation, so give it a few ticks to reach a fixed point.
async function settle() {
    for (let i = 0; i < 5; i++) await nextTick();
}

// visibleValues is the live reactive tree on the hot path, so take a plain snapshot.
function clone(values) {
    return JSON.parse(JSON.stringify(values));
}

function setValues(payload) {
    return payload.bard[0].attrs.values;
}

function setAValues(payload) {
    return payload.bard_a[0].attrs.values;
}

describe('bard set save payload when the field is never scrolled into view', () => {
    test('the editor is never built, so there are no set node views', async () => {
        const wrapper = mountBard();
        await settle();

        expect(wrapper.findComponent(BardFieldtype).vm.editor).toBeNull();
        expect(wrapper.find('[data-set-body]').exists()).toBe(false);

        wrapper.unmount();
    });

    // The expected payloads here are the ones BardSetPayload.test.js pins for a set that
    // does have node views, collapsed or expanded. Whether the field happened to scroll
    // past must not be an input to what gets saved.
    test('conditioned fields are still omitted from the payload', async () => {
        const wrapper = mountBard();
        await settle();

        expect(setValues(clone(wrapper.vm.visibleValues))).toEqual({
            type: 'main',
            always: 'A',
            local_driver: 'no',
        });

        wrapper.unmount();
    });

    test('the payload keeps up when a field outside the bard flips a condition inside it', async () => {
        const wrapper = mountBard();
        await settle();

        wrapper.vm.setFieldValue('driver', 'yes');
        await settle();

        expect(setValues(clone(wrapper.vm.visibleValues))).toEqual({
            type: 'main',
            always: 'A',
            local_driver: 'no',
            root_conditioned: 'R',
        });

        wrapper.vm.setFieldValue('driver', 'no');
        await settle();

        expect(setValues(clone(wrapper.vm.visibleValues))).toEqual({
            type: 'main',
            always: 'A',
            local_driver: 'no',
        });

        wrapper.unmount();
    });

    test('the payload keeps up when a field inside the set flips a condition inside it', async () => {
        const wrapper = mountBard();
        await settle();

        wrapper.vm.setFieldValue('bard.0.attrs.values.local_driver', 'yes');
        await settle();

        expect(setValues(clone(wrapper.vm.visibleValues))).toEqual({
            type: 'main',
            always: 'A',
            local_driver: 'yes',
            local_conditioned: 'L',
        });

        wrapper.unmount();
    });

    // `$parent.` is resolved by walking up the field path, and a Bard set's path has two
    // segments a Replicator's doesn't, so the walk misses and the condition never passes.
    // A mounted set has the same bug, but there the field is at least on screen; here the
    // wrong answer would quietly drop a value nobody has ever looked at. Until the
    // resolution itself is fixed, an unresolvable condition has to keep its value.
    test('a condition the field path cannot resolve keeps its value in the payload', async () => {
        const wrapper = mountBard(parentSetConfig, parentInitialValues());
        await settle();

        expect(setValues(clone(wrapper.vm.visibleValues))).toEqual({
            type: 'main',
            always: 'A',
            parent_conditioned: 'P',
        });

        wrapper.unmount();
    });

    // A mounted set keeps `revealer_conditioned` — the revealer is registered, so the
    // condition targeting it is filtered out before anything decides to omit the value.
    // Here the revealer never mounts, so the condition looks like an ordinary failing one
    // and the value would be dropped. Nothing can force a mount without an editor, so the
    // evaluator has to decline instead.
    test('a field gated on a revealer that never registered keeps its value in the payload', async () => {
        const wrapper = mountBard(revealerSetConfig, revealerInitialValues());
        await settle();

        expect(setValues(clone(wrapper.vm.visibleValues))).toEqual({
            type: 'main',
            always: 'A',
            revealer_conditioned: 'V',
        });

        wrapper.unmount();
    });

    // The same missing registration seen from the far side: the revealer is in a different
    // field, so this one's own set configs give no sign of it. What it can see is that the
    // condition points at a path the root values don't have — which is true of every
    // unregistered revealer, since a revealer's own value is always omitted.
    test('a field gated on a revealer in another never-rendered bard keeps its value', async () => {
        const sets = crossBardSets('rev');
        const wrapper = mountTwoBards(sets.a, sets.revealer, crossBardValues());
        await settle();

        // `local_conditioned` fails an ordinary condition and would normally be dropped.
        // Keeping it is the accepted cost of declining for the whole field.
        expect(setAValues(clone(wrapper.vm.visibleValues))).toEqual({
            type: 'main',
            always: 'A',
            local_driver: 'no',
            local_conditioned: 'L',
            gated: 'G',
        });

        // Registering the revealer is what a mounted one would do, and it's what used to
        // be the only thing keeping the value. The decline covers it either way now.
        wrapper.vm.setRevealerField('bard_b.0.attrs.values.rev');
        await settle();

        expect(setAValues(clone(wrapper.vm.visibleValues)).gated).toBe('G');

        wrapper.unmount();
    });

    // Pins that the decline turns on for an absent path, not for reaching into another
    // field at all. `flag` is present, so everything here resolves and is evaluated.
    test('a condition reaching a field that is present in another bard is still evaluated', async () => {
        const sets = crossBardSets('flag');
        const wrapper = mountTwoBards(sets.a, sets.plain, crossBardValues({ flag: 'no' }));
        await settle();

        expect(setAValues(clone(wrapper.vm.visibleValues))).toEqual({
            type: 'main',
            always: 'A',
            local_driver: 'no',
        });

        wrapper.vm.setFieldValue('bard_b.0.attrs.values.flag', 'yes');
        await settle();

        expect(setAValues(clone(wrapper.vm.visibleValues))).toEqual({
            type: 'main',
            always: 'A',
            local_driver: 'no',
            gated: 'G',
        });

        wrapper.unmount();
    });
});
