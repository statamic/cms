import { afterEach, expect, test } from 'vitest';
import { mount } from '@vue/test-utils';
import { h, nextTick, Teleport } from 'vue';
import * as Globals from '@/bootstrap/globals';
import Group from '@/components/fieldtypes/GroupFieldtype.vue';
import Text from '@/components/fieldtypes/TextFieldtype.vue';
import Textarea from '@/components/fieldtypes/TextareaFieldtype.vue';
import Select from '@/components/fieldtypes/SelectFieldtype.vue';
import ElementContainer from '@/components/ElementContainer.vue';
import Container from '@/components/ui/Publish/Container.vue';
import Fields from '@/components/ui/Publish/Fields.vue';
import { Button, InputGroup, InputGroupAppend, InputGroupPrepend } from '@ui';
import FieldsProvider from '@/components/ui/Publish/FieldsProvider.vue';
import '../../../css/app.css';

Object.assign(window, Globals);
window.__ = key => key ?? '';
const components = {
    'group-fieldtype': Group,
    'text-fieldtype': Text,
    'textarea-fieldtype': Textarea,
    'select-fieldtype': Select,
};
window.Statamic = {
    $app: { component: name => components[name] },
    $config: { get: key => key === 'sites' ? [{ handle: 'default', direction: 'ltr' }] : undefined },
    $dirty: { has: () => false, add: () => {}, remove: () => {} },
    $events: { $emit: () => {} },
    $fieldActions: { get: () => [] },
};

let wrapper;
let host;
afterEach(() => {
    wrapper?.unmount();
    host?.remove();
});

const text = (handle, width = 100) => ({ handle, display: handle, type: 'text', width });
const group = (handle, fields, options = {}) => ({ handle, display: handle, type: 'group', fields, border: false, ...options });

async function render(fields, width, values = {}, meta = {}) {
    host = document.createElement('div');
    host.style.cssText = `container: panel / inline-size; width: ${width}px;`;
    document.body.append(host);
    wrapper = mount(Container, {
        attachTo: host,
        props: { blueprint: { tabs: [] }, modelValue: values, meta, site: 'default' },
        global: {
            components: {
                ...components,
                'ui-button': Button,
                'ui-input-group': InputGroup,
                'ui-input-group-append': InputGroupAppend,
                'ui-input-group-prepend': InputGroupPrepend,
                'element-container': ElementContainer,
                portal: {
                    props: ['disabled', 'name', 'provide'],
                    setup: (props, { slots }) => () => h(Teleport, { to: 'body', disabled: props.disabled }, slots.default()),
                },
                'publish-field-fullscreen-header': { template: '<div />' },
            },
            directives: { tooltip: () => {} },
            mocks: { __: window.__ },
        },
        slots: { default: () => h(FieldsProvider, { fields }, () => h(Fields)) },
    });
    await nextTick();
    await new Promise(resolve => requestAnimationFrame(resolve));
}

function rectangle(id) {
    return document.getElementById(`field_${id}`).getBoundingClientRect();
}

function assertStacked(first, second) {
    const a = rectangle(first);
    const b = rectangle(second);
    expect(Math.abs(a.left - b.left)).toBeLessThan(1);
    expect(Math.abs(a.width - b.width)).toBeLessThan(1);
    expect(b.top).toBeGreaterThanOrEqual(a.bottom);
}

test.each([600, 900, 1200])('a 25% group stays inside a %ipx publish panel', async width => {
    await render([text('heading', 75), group('options', [text('tag'), text('design')], { width: 25 })], width);
    const groupElement = document.getElementById('field_options_tag').closest('.group-fieldtype').getBoundingClientRect();
    for (const name of ['options_tag', 'options_design']) {
        const input = rectangle(name);
        expect(input.right).toBeLessThanOrEqual(groupElement.right + 1);
        expect(Math.abs(input.width - groupElement.width)).toBeLessThan(1);
    }
    assertStacked('options_tag', 'options_design');
});

test.each([25, 33, 50, 66, 75])('a %i%% group stacks its inner fields below the breakpoint', async width => {
    await render([group('options', [text('first', 50), text('second', 50)], { width })], 600);
    assertStacked('options_first', 'options_second');
});

test.each([false, true])('a wide group preserves 50/50 columns with border=%s', async border => {
    await render([group('options', [text('first', 50), text('second', 50)], { border })], 1000);
    const a = rectangle('options_first');
    const b = rectangle('options_second');
    expect(Math.abs(a.top - b.top)).toBeLessThan(1);
    expect(b.left).toBeGreaterThan(a.right);
    expect(Math.abs(a.width - b.width)).toBeLessThan(1);
});

test('nested groups do not inherit a parent column span', async () => {
    await render([group('outer', [text('heading', 75), group('inner', [text('first', 50), text('second', 50)], { width: 25 })])], 1000);
    assertStacked('outer_inner_first', 'outer_inner_second');
    const input = document.getElementById('field_outer_inner_first');
    const bounds = input.closest('.group-fieldtype').getBoundingClientRect();
    expect(Math.abs(input.getBoundingClientRect().width - bounds.width)).toBeLessThan(1);
});

test('a narrow publish panel stacks root fields', async () => {
    await render([text('first', 50), text('second', 50)], 300);
    assertStacked('first', 'second');
});

test('fullscreen expands a narrow group and restores its columns', async () => {
    await render([group('options', [text('first', 50), text('second', 50)], { width: 25, fullscreen: true })], 1000);
    assertStacked('options_first', 'options_second');
    wrapper.findComponent(Group).vm.toggleFullscreen();
    await nextTick();
    const a = rectangle('options_first');
    const b = rectangle('options_second');
    expect(Math.abs(a.top - b.top)).toBeLessThan(1);
    expect(b.left).toBeGreaterThan(a.right);
    wrapper.findComponent(Group).vm.toggleFullscreen();
    await nextTick();
    assertStacked('options_first', 'options_second');
});

test.each([511, 512])('a group uses its own %ipx breakpoint width', async width => {
    await render([group('options', [text('first', 50), text('second', 50)])], width);
    if (width < 512) {
        assertStacked('options_first', 'options_second');
    } else {
        expect(Math.abs(rectangle('options_first').top - rectangle('options_second').top)).toBeLessThan(1);
        expect(rectangle('options_second').left).toBeGreaterThan(rectangle('options_first').right);
    }
});

test('editing a nested field updates its form value', async () => {
    await render([group('options', [text('first'), text('second')], { width: 25 })], 1000, { options: { first: 'Original', second: 'Retained' } });
    await wrapper.find('#field_options_first').setValue('Updated');
    expect(wrapper.vm.values).toEqual({ options: { first: 'Updated', second: 'Retained' } });
});

test('collapsing and expanding preserves a narrow group layout', async () => {
    await render([group('options', [text('first'), text('second')], { width: 25, collapsible: true })], 1000);
    const component = wrapper.findComponent(Group);
    component.vm.toggleCollapsed();
    await nextTick();
    expect(rectangle('options_first').width).toBe(0);
    component.vm.toggleCollapsed();
    await nextTick();
    assertStacked('options_first', 'options_second');
});

test.each([300, 1000])('spacers follow the local group width at %ipx', async width => {
    await render([group('options', [text('first', 50), { type: 'spacer', handle: 'spacer', width: 50 }, text('second', 50)])], width);
    const spacer = wrapper.find('.spacer-fieldtype').element;
    expect(getComputedStyle(spacer).display === 'none').toBe(width < 512);
});

test('select controls fit a narrow borderless group beside a textarea', async () => {
    await render([
        { handle: 'heading', display: 'Heading', type: 'textarea', width: 75 },
        group('options', [
            { handle: 'tag', display: 'Tag', type: 'select', options: { h2: 'H2', h3: 'H3' } },
            { handle: 'style', display: 'Style', type: 'select', options: { regular: 'Regular', small: 'Small' } },
        ], { width: 25, hide_display: true }),
    ], 900, { options: { tag: 'h2', style: 'regular' } }, { options: { tag: {}, style: {} } });

    const controls = wrapper.findAll('[data-ui-combobox-trigger]');
    expect(controls).toHaveLength(2);

    for (const control of controls) {
        const bounds = control.element.closest('.group-fieldtype').getBoundingClientRect();
        const input = control.element.getBoundingClientRect();
        expect(input.left).toBeGreaterThanOrEqual(bounds.left - 1);
        expect(input.right).toBeLessThanOrEqual(bounds.right + 1);
    }

    expect(controls[1].element.getBoundingClientRect().top)
        .toBeGreaterThan(controls[0].element.getBoundingClientRect().bottom);
});
