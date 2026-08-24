import { mount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';
import { defineComponent, h, nextTick, reactive, ref } from 'vue';
import * as ui from '@ui';
import * as Globals from '@/bootstrap/globals';
import TextFieldtype from '@/components/fieldtypes/TextFieldtype.vue';
import RevealerFieldtype from '@/components/fieldtypes/RevealerFieldtype.vue';
import Container, { injectContainerContext } from '@/components/ui/Publish/Container.vue';
import Set from '@/components/fieldtypes/bard/Set.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;
window.$markdown = (value) => value;

window.Statamic = {
    $app: {
        component: (name) => ({ 'text-fieldtype': TextFieldtype, 'revealer-fieldtype': RevealerFieldtype })[name],
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

const revealerSetConfig = {
    handle: 'main',
    display: 'Main',
    fields: [
        { handle: 'always', type: 'text' },
        { handle: 'rev', type: 'revealer' },
        { handle: 'revealer_conditioned', type: 'text', if: { rev: 'equals true' } },
    ],
};

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

// Stands in for the Bard fieldtype: builds the tiptap node view props and the `bard`
// vm that Set.configure({ bard: this }) hands the extension. The prosemirror editor
// itself plays no part in condition bookkeeping, so it isn't built here.
function createSetHost({ collapsed, hasError, set, values }) {
    const node = reactive({
        attrs: {
            id: 'set-1',
            enabled: true,
            values: values().bard[0].attrs.values,
        },
    });

    const bard = reactive({
        collapsed: collapsed ? ['set-1'] : [],
        meta: { existing: { 'set-1': {} } },
        setIndexes: { 'set-1': 0 },
        fieldPathPrefix: null,
        metaPathPrefix: null,
        handle: 'bard',
        name: 'bard',
        setConfigs: [set],
        isReadOnly: false,
        hasBeenFocused: false,
        dragging: false,
        config: { previews: true },
        setHasError: () => hasError.value,
        collapseSet: (id) => {
            if (!bard.collapsed.includes(id)) bard.collapsed.push(id);
        },
        expandSet: (id) => {
            const index = bard.collapsed.indexOf(id);
            if (index !== -1) bard.collapsed.splice(index, 1);
        },
        collapseAll: () => {},
        duplicateSet: () => {},
        $emit: () => {},
        $el: document.createElement('div'),
    });

    const host = defineComponent({
        setup() {
            injectContainerContext();

            return () =>
                h(Set, {
                    node,
                    decorations: [],
                    selected: false,
                    editor: {},
                    extension: { options: { bard } },
                    getPos: () => 0,
                    updateAttributes: (attrs) => Object.assign(node.attrs, attrs),
                    deleteNode: () => {},
                });
        },
    });

    return { host, bard };
}

function mountSet({ collapsed = false, hasError = ref(false), set = setConfig, values = initialValues } = {}) {
    const { host, bard } = createSetHost({ collapsed, hasError, set, values });

    const wrapper = mount(Container, {
        props: {
            blueprint: { tabs: [] },
            modelValue: values(),
            site: 'default',
        },
        global: {
            components: {
                ...uiComponents,
                'text-fieldtype': TextFieldtype,
                'revealer-fieldtype': RevealerFieldtype,
            },
            provide: {
                bard,
                bardSets: [],
                // Mount synchronously rather than waiting for an idle callback.
                mountScheduler: { schedule: (callback) => callback() },
                // Required by tiptap's NodeViewWrapper.
                onDragStart: () => {},
                decorationClasses: '',
            },
        },
        slots: {
            default: () => h(host),
        },
    });

    return { wrapper, bard };
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

async function payloadsFor(collapsed) {
    const { wrapper } = mountSet({ collapsed });
    await settle();

    const initial = clone(wrapper.vm.visibleValues);

    // Flip the top-level field that the set's `$root.` condition points at.
    wrapper.vm.setFieldValue('driver', 'yes');
    await settle();

    const afterOutsideFlip = clone(wrapper.vm.visibleValues);

    // Flip the outside field back, so the condition goes from passing to failing.
    wrapper.vm.setFieldValue('driver', 'no');
    await settle();

    const afterOutsideFlipBack = clone(wrapper.vm.visibleValues);

    wrapper.unmount();

    return { initial, afterOutsideFlip, afterOutsideFlipBack };
}

describe('bard set save payload', () => {
    describe('the payload is the same whether the set is collapsed or expanded', () => {
        test('across a field outside the set flipping a condition inside it', async () => {
            const expanded = await payloadsFor(false);
            const collapsed = await payloadsFor(true);

            expect(collapsed.initial).toEqual(expanded.initial);
            expect(collapsed.afterOutsideFlip).toEqual(expanded.afterOutsideFlip);
            expect(collapsed.afterOutsideFlipBack).toEqual(expanded.afterOutsideFlipBack);
        });
    });

    // Pins what the payload actually is, so the equality tests above can't pass by
    // both sides being equally wrong.
    describe('the expanded payload is correct', () => {
        test('through the whole cycle', async () => {
            const expanded = await payloadsFor(false);

            expect(expanded.initial.bard[0].attrs.values).toEqual({
                type: 'main',
                always: 'A',
                local_driver: 'no',
            });

            expect(expanded.afterOutsideFlip.bard[0].attrs.values).toEqual({
                type: 'main',
                always: 'A',
                local_driver: 'no',
                root_conditioned: 'R',
            });

            expect(expanded.afterOutsideFlipBack.bard[0].attrs.values).toEqual({
                type: 'main',
                always: 'A',
                local_driver: 'no',
            });
        });
    });

    // The payload BardUnrenderedPayload.test.js expects a never-scrolled field to produce.
    // A set containing a revealer is never allowed to defer its mount, so this holds
    // collapsed as well as expanded.
    describe('a field gated on a revealer', () => {
        test.each([false, true])('keeps its value when collapsed is %s', async (collapsed) => {
            const { wrapper } = mountSet({ collapsed, set: revealerSetConfig, values: revealerInitialValues });
            await settle();

            expect(wrapper.findComponent(RevealerFieldtype).exists()).toBe(true);

            expect(clone(wrapper.vm.visibleValues).bard[0].attrs.values).toEqual({
                type: 'main',
                always: 'A',
                revealer_conditioned: 'V',
            });

            wrapper.unmount();
        });
    });

    describe('validation errors', () => {
        test('a collapsed set expands when errors arrive without a remount', async () => {
            const hasError = ref(false);
            const { wrapper, bard } = mountSet({ collapsed: true, hasError });
            await settle();

            expect(bard.collapsed).toEqual(['set-1']);
            expect(wrapper.find('[data-set-body]').exists()).toBe(false);

            hasError.value = true;
            await settle();

            expect(bard.collapsed).toEqual([]);
            expect(wrapper.find('[data-set-body]').exists()).toBe(true);

            wrapper.unmount();
        });
    });
});
