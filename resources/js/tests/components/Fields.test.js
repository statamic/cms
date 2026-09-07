import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { defineComponent, h, nextTick } from 'vue';
import * as Globals from '@/bootstrap/globals';
import HiddenFieldtype from '@/components/fieldtypes/HiddenFieldtype.vue';
import Container, { injectContainerContext } from '@/components/ui/Publish/Container.vue';
import Fields from '@/components/ui/Publish/Fields.vue';
import FieldsProvider from '@/components/ui/Publish/FieldsProvider.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;

window.Statamic = {
    $app: { component: (name) => (name === 'hidden-fieldtype' ? HiddenFieldtype : undefined) },
    $config: {
        get: (key) => (key === 'sites' ? [{ handle: 'default', direction: 'ltr' }] : undefined),
    },
    $dirty: { has: () => false, add: () => {}, remove: () => {} },
    $events: { $emit: () => {} },
};

const fields = [
    { handle: 'protected', type: 'toggle' },
    { handle: 'scheme', type: 'hidden', replicator_preview: true, if: { protected: 'equals true' } },
];

let previews;

const Probe = defineComponent({
    setup() {
        previews = injectContainerContext().previews;
        return () => h('div');
    },
});

async function mountFields(values, provide = {}) {
    const wrapper = mount(Container, {
        props: {
            blueprint: { tabs: [] },
            modelValue: values,
            site: 'default',
        },
        global: {
            components: { 'hidden-fieldtype': HiddenFieldtype },
            provide,
        },
        slots: {
            default: () => [h(Probe), h(FieldsProvider, { fields }, () => h(Fields))],
        },
    });

    await nextTick();

    return wrapper;
}

test('a hidden fieldtype is not visible', async () => {
    const wrapper = await mountFields({ protected: true, scheme: 'password' });

    expect(wrapper.find('.hidden-fieldtype').attributes('style')).toBe('display: none;');
});

test('a hidden fieldtype is visible on a form submission', async () => {
    const wrapper = await mountFields({ protected: true, scheme: 'password' }, { isFormSubmission: true });

    expect(wrapper.find('.hidden-fieldtype').attributes('style')).toBeUndefined();
});

test('the value of a hidden fieldtype is omitted when its conditions fail', async () => {
    const wrapper = await mountFields({ protected: false, scheme: 'password' });

    expect(wrapper.vm.visibleValues).toEqual({ protected: false });
});

test('the value of a hidden fieldtype is kept when its conditions pass', async () => {
    const wrapper = await mountFields({ protected: true, scheme: 'password' });

    expect(wrapper.vm.visibleValues).toEqual({ protected: true, scheme: 'password' });
});

test('a hidden fieldtype does not get a replicator preview', async () => {
    await mountFields({ protected: true, scheme: 'password' }, { showReplicatorFieldPreviews: true });

    expect(previews.value).toEqual({});
});
