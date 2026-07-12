import { mount, flushPromises } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import Stack from '@/components/ui/Stack/Stack.vue';

test('Stack forwards Cmd+S keyboard shortcut to document', async () => {
    const documentSpy = vi.spyOn(document, 'dispatchEvent');

    const wrapper = mount(Stack, {
        props: {
            open: true,
            title: 'Test Stack',
        },
        slots: {
            default: 'Test content',
        },
        global: {
            stubs: {
                Teleport: true,
            },
        },
    });

    await flushPromises();

    // Find the stack-content div
    const stackContent = wrapper.find('.stack-content');
    expect(stackContent.exists()).toBe(true);

    // Trigger Cmd+S keydown event
    const event = new KeyboardEvent('keydown', {
        key: 's',
        code: 'KeyS',
        metaKey: true,
        bubbles: true,
        cancelable: true,
    });

    stackContent.element.dispatchEvent(event);

    // Check if dispatchEvent was called (forwarding the shortcut)
    expect(documentSpy).toHaveBeenCalled();

    documentSpy.mockRestore();
});

test('Stack does not forward non-shortcut keys', async () => {
    const documentSpy = vi.spyOn(document, 'dispatchEvent');

    const wrapper = mount(Stack, {
        props: {
            open: true,
            title: 'Test Stack',
        },
        slots: {
            default: 'Test content',
        },
        global: {
            stubs: {
                Teleport: true,
            },
        },
    });

    await flushPromises();

    const stackContent = wrapper.find('.stack-content');

    // Trigger a regular keydown event
    const event = new KeyboardEvent('keydown', {
        key: 'a',
        code: 'KeyA',
        bubbles: true,
        cancelable: true,
    });

    stackContent.element.dispatchEvent(event);

    // Check that dispatchEvent was not called for regular keys
    expect(documentSpy).not.toHaveBeenCalled();

    documentSpy.mockRestore();
});
