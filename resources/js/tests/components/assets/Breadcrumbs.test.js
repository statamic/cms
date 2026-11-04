import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import Breadcrumbs from '@/components/assets/Browser/Breadcrumbs.vue';

const stubs = {
    'ui-icon': true,
    'ui-button': { template: '<button><slot />{{ $attrs.text }}</button>' },
};

const breadcrumbs = (props) => mount(Breadcrumbs, { props: { path: 'content/photos', ...props }, global: { stubs } });

const dragEvent = (wrapper, index, type) => {
    const event = new Event(type, { bubbles: true, cancelable: true });
    wrapper.findAll('button')[index].element.dispatchEvent(event);

    return event;
};

test('dropping on a parent breadcrumb emits its path', () => {
    const wrapper = breadcrumbs({ droppable: true });

    expect(dragEvent(wrapper, 0, 'dragover').defaultPrevented).toBe(true);
    dragEvent(wrapper, 0, 'drop');
    dragEvent(wrapper, 1, 'drop');

    expect(wrapper.emitted('dropped')).toEqual([['/'], ['content']]);
});

test('breadcrumbs do not accept drops when nothing movable is being dragged', () => {
    const wrapper = breadcrumbs({ droppable: false });

    expect(dragEvent(wrapper, 0, 'dragover').defaultPrevented).toBe(false);
    dragEvent(wrapper, 0, 'drop');

    expect(wrapper.emitted('dropped')).toBeUndefined();
});

test('the current folder only accepts drops when it is allowed', () => {
    const wrapper = breadcrumbs({ droppable: true });

    expect(dragEvent(wrapper, 2, 'dragover').defaultPrevented).toBe(false);
    dragEvent(wrapper, 2, 'drop');
    expect(wrapper.emitted('dropped')).toBeUndefined();

    const searching = breadcrumbs({ droppable: true, currentDroppable: true });

    dragEvent(searching, 2, 'drop');
    expect(searching.emitted('dropped')).toEqual([['content/photos']]);
});
