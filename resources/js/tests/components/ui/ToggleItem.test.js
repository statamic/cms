import { mount } from '@vue/test-utils';
import { h } from 'vue';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { ToggleGroup, ToggleItem } from '@/components/ui';
import { useTooltip } from '@/composables/tooltip';

function resetTooltip() {
    const { hide, targetEl, dismissFor } = useTooltip();

    if (targetEl.value) {
        dismissFor(targetEl.value);
    }

    hide();
}

function mountGroup(items) {
    return mount(ToggleGroup, {
        props: { modelValue: items[0].value },
        slots: {
            default: () => items.map((item) => h(ToggleItem, item)),
        },
        global: {
            stubs: { Icon: true },
        },
    });
}

beforeEach(() => {
    vi.useFakeTimers();
    resetTooltip();
});

afterEach(() => {
    resetTooltip();
    vi.useRealTimers();
});

test('tooltip is the accessible name and shows on hover', async () => {
    const { isVisible, content } = useTooltip();
    const wrapper = mountGroup([
        { value: 'percent', label: '%', tooltip: 'Percentage' },
        { value: 'count', icon: 'layout-list', tooltip: 'Response count' },
    ]);

    const buttons = wrapper.findAll('button');

    expect(buttons[0].attributes('aria-label')).toBe('Percentage');
    expect(buttons[0].text()).toBe('%');
    expect(buttons[1].attributes('aria-label')).toBe('Response count');

    await buttons[0].trigger('mouseenter');
    await vi.advanceTimersByTimeAsync(200);

    expect(isVisible.value).toBe(true);
    expect(content.value).toBe('Percentage');

    await buttons[0].trigger('mouseleave');
    await vi.advanceTimersByTimeAsync(50);

    expect(isVisible.value).toBe(false);
});

test('an explicit aria-label wins over the tooltip', () => {
    const wrapper = mountGroup([
        { value: 'list', icon: 'layout-list', tooltip: 'List', 'aria-label': 'List view' },
    ]);

    expect(wrapper.get('button').attributes('aria-label')).toBe('List view');
});

test('an explicit aria-label is kept when there is no tooltip', () => {
    const wrapper = mountGroup([
        { value: 'list', icon: 'layout-list', 'aria-label': 'List view' },
    ]);

    expect(wrapper.get('button').attributes('aria-label')).toBe('List view');
});
