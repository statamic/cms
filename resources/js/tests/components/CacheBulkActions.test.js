import { enableAutoUnmount, mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { defineComponent, h, ref } from 'vue';
import { provideListingContext } from '@ui/Listing/Listing.vue';
import CacheBulkActions from '@/components/utilities/CacheBulkActions.vue';
import * as Globals from '@/bootstrap/globals';

enableAutoUnmount(afterEach);

beforeEach(() => {
    vi.useFakeTimers();
    vi.stubGlobal('ResizeObserver', class {
        observe() {}
        unobserve() {}
        disconnect() {}
    });
    Object.assign(window, Globals);
});

afterEach(() => {
    vi.useRealTimers();
    vi.unstubAllGlobals();
});

function mountActions({ inStack = false } = {}) {
    const selections = ref(['first', 'second']);
    const processing = ref(false);
    const provider = defineComponent({
        setup() {
            provideListingContext({
                selections,
                clearSelections: () => selections.value = [],
            });

            return () => h('div', { class: inStack ? 'stack-content' : undefined }, [
                h(CacheBulkActions, { label: 'Clear', processing: processing.value }),
            ]);
        },
    });

    const wrapper = mount(provider, {
        attachTo: document.body,
        global: {
            stubs: { Icon: true },
            mocks: { __: Globals.__, __n: Globals.__n },
        },
    });

    return { wrapper, selections, processing };
}

test('uses the native floating toolbar to act on selections and deselect them', async () => {
    const { wrapper, selections } = mountActions();
    expect(wrapper.find('[data-floating-toolbar]').exists()).toBe(false);
    await vi.advanceTimersByTimeAsync(300);

    expect(wrapper.text()).toContain('Deselect 2 items');
    const clear = wrapper.findAll('button').find((button) => button.text().startsWith('Clear'));
    await clear.trigger('click');
    expect(wrapper.findComponent(CacheBulkActions).emitted('run')).toEqual([[['first', 'second']]]);

    const deselect = wrapper.findAll('button').find((button) => button.text().startsWith('Deselect'));
    await deselect.trigger('click');
    expect(selections.value).toEqual([]);
    expect(wrapper.find('[data-floating-toolbar]').exists()).toBe(false);
});

test('supports native Escape deselection and blocks actions while processing', async () => {
    const { wrapper, selections, processing } = mountActions();
    await vi.advanceTimersByTimeAsync(300);
    processing.value = true;
    await vi.advanceTimersByTimeAsync(0);

    await wrapper.findAll('button').find((button) => button.text().startsWith('Clear')).trigger('click');
    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true, cancelable: true }));
    expect(wrapper.findComponent(CacheBulkActions).emitted('run')).toBeUndefined();
    expect(selections.value).toHaveLength(2);

    processing.value = false;
    await vi.advanceTimersByTimeAsync(0);
    document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true, cancelable: true }));
    expect(selections.value).toEqual([]);
});

test('handles shortcuts inside its own stack and defers to a nested overlay', async () => {
    const { wrapper, selections } = mountActions({ inStack: true });
    await vi.advanceTimersByTimeAsync(300);

    const overlay = document.createElement('div');
    overlay.setAttribute('role', 'dialog');
    wrapper.element.append(overlay);

    overlay.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true, cancelable: true }));
    expect(selections.value).toHaveLength(2);
    overlay.remove();

    wrapper.element.dispatchEvent(new KeyboardEvent('keydown', { key: 'c', bubbles: true, cancelable: true }));
    expect(wrapper.findComponent(CacheBulkActions).emitted('run')).toEqual([[['first', 'second']]]);

    wrapper.element.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true, cancelable: true }));
    expect(selections.value).toEqual([]);
});
