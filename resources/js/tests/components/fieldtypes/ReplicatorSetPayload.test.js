import { mount } from '@vue/test-utils';
import { beforeAll, describe, expect, test } from 'vitest';
import { defineComponent, h, nextTick, ref } from 'vue';
import * as ui from '@ui';
import * as Globals from '@/bootstrap/globals';
import TextFieldtype from '@/components/fieldtypes/TextFieldtype.vue';
import Container, { injectContainerContext } from '@/components/ui/Publish/Container.vue';
import Set from '@/components/fieldtypes/replicator/Set.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;

window.Statamic = {
    $app: { component: (name) => (name === 'text-fieldtype' ? TextFieldtype : undefined) },
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
        { handle: 'parent_conditioned', type: 'text', if: { '$parent.driver': 'equals yes' } },
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
                local_driver: 'no',
                local_conditioned: 'L',
                root_conditioned: 'R',
                parent_conditioned: 'P',
            },
        ],
    };
}

// Stands in for Replicator.vue: renders a single set, wired to the container's values
// the same way the real fieldtype does.
const SetHost = defineComponent({
    props: { collapsed: Boolean, config: Object, hasError: Boolean },
    setup(props) {
        const { values } = injectContainerContext();

        return () =>
            h(Set, {
                config: props.config ?? setConfig,
                id: 'set-1',
                fieldPath: 'rep',
                metaPath: 'rep',
                index: 0,
                collapsed: props.collapsed,
                hasError: props.hasError,
                values: values.value.rep[0],
                enabled: true,
            });
    },
});

function mountSet(collapsed, config) {
    return mount(Container, {
        props: {
            blueprint: { tabs: [] },
            modelValue: initialValues(),
            site: 'default',
        },
        global: {
            components: { ...uiComponents, 'text-fieldtype': TextFieldtype },
            provide: {
                replicatorSets: [],
                // Mount synchronously rather than waiting for an idle callback.
                mountScheduler: { schedule: (callback) => callback() },
            },
            stubs: { 'confirmation-modal': true },
        },
        slots: {
            default: () => h(SetHost, { collapsed, config }),
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

async function payloadsFor(collapsed) {
    const wrapper = mountSet(collapsed);
    await settle();

    const initial = clone(wrapper.vm.visibleValues);

    // Flip the top-level field that the set's `$root.` and `$parent.` conditions point at.
    wrapper.vm.setFieldValue('driver', 'yes');
    await settle();

    const afterOutsideFlip = clone(wrapper.vm.visibleValues);

    // Flip the set-local field that the set's plain-handle condition points at.
    wrapper.vm.setFieldValue('rep.0.local_driver', 'yes');
    await settle();

    const afterInsideFlip = clone(wrapper.vm.visibleValues);

    // Flip the outside field back, so the conditions go from passing to failing.
    wrapper.vm.setFieldValue('driver', 'no');
    await settle();

    const afterOutsideFlipBack = clone(wrapper.vm.visibleValues);

    wrapper.unmount();

    return { initial, afterOutsideFlip, afterInsideFlip, afterOutsideFlipBack };
}

describe('replicator set save payload', () => {
    let expanded;
    let collapsed;

    beforeAll(async () => {
        expanded = await payloadsFor(false);
        collapsed = await payloadsFor(true);
    });

    describe('the payload is the same whether the set is collapsed or expanded', () => {
        test('on load', () => {
            expect(collapsed.initial).toEqual(expanded.initial);
        });

        test('after a field outside the set flips a condition inside it', () => {
            expect(collapsed.afterOutsideFlip).toEqual(expanded.afterOutsideFlip);
        });

        test('after a field inside the set flips a condition inside it', () => {
            expect(collapsed.afterInsideFlip).toEqual(expanded.afterInsideFlip);
        });

        test('after a field outside the set flips a condition inside it back', () => {
            expect(collapsed.afterOutsideFlipBack).toEqual(expanded.afterOutsideFlipBack);
        });
    });

    // Pins what the payload actually is, so the equality tests above can't pass by
    // both sides being equally wrong.
    describe('the expanded payload is correct', () => {
        test('on load', () => {
            expect(expanded.initial.rep[0]).toEqual({
                id: 'set-1',
                type: 'main',
                enabled: true,
                always: 'A',
                local_driver: 'no',
            });
        });

        test('after a field outside the set flips a condition inside it', () => {
            expect(expanded.afterOutsideFlip.rep[0]).toEqual({
                id: 'set-1',
                type: 'main',
                enabled: true,
                always: 'A',
                local_driver: 'no',
                root_conditioned: 'R',
                parent_conditioned: 'P',
            });
        });

        test('after a field inside the set flips a condition inside it', () => {
            expect(expanded.afterInsideFlip.rep[0]).toEqual({
                id: 'set-1',
                type: 'main',
                enabled: true,
                always: 'A',
                local_driver: 'yes',
                local_conditioned: 'L',
                root_conditioned: 'R',
                parent_conditioned: 'P',
            });
        });

        test('after a field outside the set flips a condition inside it back', () => {
            expect(expanded.afterOutsideFlipBack.rep[0]).toEqual({
                id: 'set-1',
                type: 'main',
                enabled: true,
                always: 'A',
                local_driver: 'yes',
                local_conditioned: 'L',
            });
        });
    });

    // Nothing evaluates the conditions of fields nested inside a set's fields, so a set
    // like this can't be left unmounted without its nested omitValue bookkeeping going
    // missing entirely.
    describe('a collapsed set whose nested fields have conditions', () => {
        test('mounts its body anyway', async () => {
            const wrapper = mountSet(true, {
                handle: 'main',
                fields: [
                    { handle: 'always', type: 'text' },
                    {
                        handle: 'grid',
                        type: 'grid',
                        fields: [
                            { handle: 'row_driver', type: 'text' },
                            { handle: 'row_conditioned', type: 'text', if: { row_driver: 'equals yes' } },
                        ],
                    },
                ],
            });

            await settle();

            expect(wrapper.find('[data-set-body]').exists()).toBe(true);

            wrapper.unmount();
        });
    });

    describe('a collapsed set whose fields are all self-contained', () => {
        test('leaves its body unmounted', async () => {
            const wrapper = mountSet(true);

            await settle();

            expect(wrapper.find('[data-set-body]').exists()).toBe(false);

            wrapper.unmount();
        });
    });

    describe('validation errors', () => {
        test('a collapsed set expands when errors arrive without a remount', async () => {
            const collapsed = ref(true);
            const hasError = ref(false);

            const wrapper = mount(Container, {
                props: { blueprint: { tabs: [] }, modelValue: initialValues(), site: 'default' },
                global: {
                    components: { ...uiComponents, 'text-fieldtype': TextFieldtype },
                    provide: {
                        replicatorSets: [],
                        mountScheduler: { schedule: (callback) => callback() },
                    },
                    stubs: { 'confirmation-modal': true },
                },
                slots: {
                    default: () =>
                        h(SetHost, {
                            collapsed: collapsed.value,
                            hasError: hasError.value,
                            onExpanded: () => (collapsed.value = false),
                        }),
                },
            });

            await settle();

            expect(wrapper.find('[data-set-body]').exists()).toBe(false);

            hasError.value = true;
            await settle();

            expect(collapsed.value).toBe(false);
            expect(wrapper.find('[data-set-body]').exists()).toBe(true);

            wrapper.unmount();
        });
    });
});
