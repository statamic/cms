import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import Uploader from '@/components/assets/Uploader.vue';

const uploader = () =>
    mount(Uploader, {
        props: { enabled: true, container: 'assets', path: '/', url: '/cp/assets' },
        slots: { default: `<template #default="{ dragging }"><span>{{ dragging ? 'dragging' : 'idle' }}</span></template>` },
    });

const dragenter = (wrapper, types) => {
    const event = new Event('dragenter', { bubbles: true, cancelable: true });
    event.dataTransfer = { types };
    wrapper.element.dispatchEvent(event);
};

test('dragging files shows the upload state', async () => {
    const wrapper = uploader();

    dragenter(wrapper, ['Files']);
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toBe('dragging');
});

test('dragging things from within the page is ignored', async () => {
    const wrapper = uploader();

    dragenter(wrapper, ['text/plain']);
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toBe('idle');
});
