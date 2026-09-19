import { afterEach, expect, test } from 'vitest';
import { mount } from '@vue/test-utils';
import { h, nextTick, Teleport } from 'vue';
import * as Globals from '@/bootstrap/globals';
import Text from '@/components/fieldtypes/TextFieldtype.vue';
import ElementContainer from '@/components/ElementContainer.vue';
import Container from '@/components/ui/Publish/Container.vue';
import Fields from '@/components/ui/Publish/Fields.vue';
import { Button } from '@ui';
import FieldsProvider from '@/components/ui/Publish/FieldsProvider.vue';
import '../../../css/app.css';

Object.assign(window, Globals);
window.__ = key => key ?? '';
const components = {
    'text-fieldtype': Text,
};
window.Statamic = {
    $app: { component: name => components[name] },
    $config: { get: key => key === 'sites' ? [{ handle: 'default', direction: 'ltr' }] : undefined },
    $dirty: { has: () => false, add: () => {}, remove: () => {} },
    $events: { $emit: () => {} },
    $fieldActions: {
        get: () => [{ title: 'Remove', run: () => {}, visible: ({ value }) => !! value }],
    },
};

let wrapper;
let host;
afterEach(() => {
    wrapper?.unmount();
    host?.remove();
});

const text = (handle) => ({ handle, display: handle, type: 'text', width: 50, actions: true });

async function render(values) {
    host = document.createElement('div');
    host.style.cssText = 'container: panel / inline-size; width: 1000px;';
    document.body.append(host);
    wrapper = mount(Container, {
        attachTo: host,
        props: { blueprint: { tabs: [] }, modelValue: values, meta: {}, site: 'default' },
        global: {
            components: {
                ...components,
                'ui-button': Button,
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
        slots: { default: () => h(FieldsProvider, { fields: [text('first'), text('second')] }, () => h(Fields)) },
    });
    await nextTick();
    await new Promise(resolve => requestAnimationFrame(resolve));
}

function rectangle(id) {
    return document.getElementById(`field_${id}`).getBoundingClientRect();
}

test('field actions do not push their field below a neighbour without them', async () => {
    await render({ first: 'One' });
    expect(Math.abs(rectangle('first').top - rectangle('second').top)).toBeLessThan(1);
});
