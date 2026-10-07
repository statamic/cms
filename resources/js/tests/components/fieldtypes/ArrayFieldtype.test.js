import { mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import ArrayFieldtype from '@/components/fieldtypes/ArrayFieldtype.vue';
import { Button, Input } from '@/components/ui';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));

function mountField({ value = {}, config = {}, readOnly = false } = {}) {
    return mount(ArrayFieldtype, {
        props: {
            value,
            handle: 'attributes',
            readOnly,
            meta: { keys: {} },
            config: { type: 'array', ...config },
        },
        global: {
            stubs: {
                'confirmation-modal': true,
                Popover: {
                    template: '<div><slot name="trigger" /><slot /></div>',
                    props: ['open', 'align', 'side', 'dismissible'],
                },
                SortableList: {
                    template: '<tbody><slot /></tbody>',
                    props: ['modelValue', 'vertical', 'itemClass', 'handleClass', 'mirror'],
                },
            },
            components: {
                'ui-input': Input,
                'ui-button': Button,
            },
        },
    });
}

function addButton(wrapper) {
    return wrapper.findAll('button').find((button) => button.text().includes('Add Row') || button.text().includes('Add Attribute'));
}

test('add button appends a row', async () => {
    const wrapper = mountField({ value: { foo: 'bar' } });

    await addButton(wrapper).trigger('click');

    expect(wrapper.vm.data).toHaveLength(2);
    expect(wrapper.vm.data[0].key).toBe('foo');
    expect(wrapper.vm.data[1].key).toBeNull();
    expect(wrapper.vm.data[1].value).toBeNull();
});

test('opening an empty compact field adds a placeholder row that is discarded on close', () => {
    const wrapper = mountField({ config: { compact: true } });

    wrapper.vm.setCompactOpen(true);
    expect(wrapper.vm.data).toHaveLength(1);
    expect(wrapper.vm.data[0].key).toBeNull();
    expect(wrapper.vm.data[0].value).toBeNull();

    wrapper.vm.setCompactOpen(false);
    expect(wrapper.vm.data).toHaveLength(0);
});

test('closing compact keeps filled rows and drops leftover empty ones', () => {
    const wrapper = mountField({
        value: { foo: 'bar' },
        config: { compact: true },
    });

    wrapper.vm.addValue();
    expect(wrapper.vm.data).toHaveLength(2);

    wrapper.vm.setCompactOpen(false);
    expect(wrapper.vm.data).toHaveLength(1);
    expect(wrapper.vm.data[0].key).toBe('foo');
});

test('read-only compact trigger can still be opened', () => {
    const wrapper = mountField({
        value: { foo: 'bar' },
        config: { compact: true },
        readOnly: true,
    });

    const trigger = wrapper.find('[aria-haspopup="dialog"]');
    expect(trigger.exists()).toBe(true);
    expect(trigger.attributes('disabled')).toBeUndefined();
});

test('enter adds a row below the current one', () => {
    const wrapper = mountField({ value: { foo: 'bar' } });
    const preventDefault = vi.fn();

    wrapper.vm.addRowOnEnter({
        target: { tagName: 'INPUT', closest: () => null },
        preventDefault,
    });

    expect(wrapper.vm.data).toHaveLength(2);
    expect(wrapper.vm.data[1].key).toBeNull();
    expect(preventDefault).toHaveBeenCalled();
});
