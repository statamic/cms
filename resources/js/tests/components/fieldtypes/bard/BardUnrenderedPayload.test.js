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

function mountBard() {
    const values = initialValues();

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
                        sets: [{ handle: 'group', sets: [setConfig] }],
                        buttons: [],
                    },
                }),
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
});
