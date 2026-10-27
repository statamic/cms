import { expect, test, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import Uploader from '@/components/assets/Uploader.vue';

function mountUploader(methods = {}) {
    return mount(
        { ...Uploader, methods: { ...Uploader.methods, ...methods } },
        {
            props: { url: '/cp/assets' },
            slots: { default: '<div class="content" />' },
        },
    );
}

function dragEvent(type, types, files = []) {
    const event = new Event(type, { bubbles: true, cancelable: true });

    Object.defineProperty(event, 'dataTransfer', {
        value: { types, files, items: [] },
    });

    return event;
}

test.each([['dragenter'], ['dragover']])('a file %s is handled', async (type) => {
    const wrapper = mountUploader();
    const event = dragEvent(type, ['Files']);

    wrapper.element.dispatchEvent(event);
    await wrapper.vm.$nextTick();

    expect(event.defaultPrevented).toBe(true);
});

test('a file dragenter sets dragging', async () => {
    const wrapper = mountUploader();

    wrapper.element.dispatchEvent(dragEvent('dragenter', ['Files']));

    expect(wrapper.vm.dragging).toBe(true);
});

test.each([['dragenter'], ['dragover'], ['drop']])('a non-file %s is ignored', (type) => {
    const wrapper = mountUploader();
    const event = dragEvent(type, ['text/plain']);

    wrapper.element.dispatchEvent(event);

    expect(event.defaultPrevented).toBe(false);
    expect(wrapper.vm.dragging).toBe(false);
});

test('a non-file drag reaches listeners further up', () => {
    const wrapper = mountUploader();
    const parent = document.createElement('div');
    parent.appendChild(wrapper.element);

    let received = false;
    parent.addEventListener('drop', () => (received = true));

    wrapper.element.querySelector('.content').dispatchEvent(dragEvent('drop', ['text/plain']));

    expect(received).toBe(true);
});

test('a file drop adds uploads', () => {
    const addFile = vi.fn();
    const wrapper = mountUploader({ addFile });
    const file = new File(['x'], 'test.txt');

    wrapper.element.dispatchEvent(dragEvent('drop', ['Files'], [file]));

    expect(addFile).toHaveBeenCalledWith(file);
});

test('a non-file drop does not add uploads', () => {
    const addFile = vi.fn();
    const wrapper = mountUploader({ addFile });
    const file = new File(['x'], 'test.txt');

    wrapper.element.dispatchEvent(dragEvent('drop', ['text/plain'], [file]));

    expect(addFile).not.toHaveBeenCalled();
});

test('types given as a DOMStringList are supported', () => {
    const wrapper = mountUploader();
    const types = {
        0: 'Files',
        length: 1,
        contains: (type) => type === 'Files',
    };
    const event = dragEvent('dragenter', types);

    wrapper.element.dispatchEvent(event);

    expect(event.defaultPrevented).toBe(true);
    expect(wrapper.vm.dragging).toBe(true);
});
