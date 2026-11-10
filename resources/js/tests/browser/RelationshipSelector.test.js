import { afterEach, expect, test, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import { h } from 'vue';
import axios from 'axios';
import * as Globals from '@/bootstrap/globals.js';
import { NumberFormatter, RelationshipSelector } from '@/bootstrap/cms/core.js';
import { Select, Badge, Icon, Skeleton, ToggleGroup, ToggleItem, InputGroup, InputGroupPrepend, InputGroupAppend } from '@ui';
import { translate, translateChoice } from '@/translations/translator.js';
import '../../../../.storybook/storybook.css';
import '../../../../.storybook/theme.css';

Object.assign(window, Globals, { __: translate, __n: translateChoice });
window.Statamic = {
    $config: { get: key => ({ paginationSize: 50, paginationSizeOptions: [50] })[key] },
    $preferences: { get: () => undefined },
    $progress: { loading() {}, complete() {} },
    $events: { $on() {}, $off() {} },
};

let host;
let wrapper;
afterEach(() => {
    wrapper?.unmount();
    host?.remove();
    vi.restoreAllMocks();
});

async function render(width, toolbar = true) {
    vi.spyOn(axios, 'get').mockResolvedValue({
        data: { data: [], meta: { from: 0, to: 0, total: 0, current_page: 1, last_page: 1, per_page: 50 } },
    });
    host = document.createElement('div');
    host.style.cssText = `width: ${width}px; height: 360px;`;
    document.body.appendChild(host);
    wrapper = mount(RelationshipSelector, {
        attachTo: host,
        props: { initialSelections: [], filtersUrl: '/filters', selectionsUrl: '/selections', maxSelections: 1 },
        slots: toolbar ? {
            toolbar: () => h(Select, { modelValue: 'all', options: [{ value: 'all', label: 'All resources' }] }),
        } : {},
        global: {
            components: {
                'ui-badge': Badge,
                'ui-icon': Icon,
                'ui-skeleton': Skeleton,
                'ui-toggle-group': ToggleGroup,
                'ui-toggle-item': ToggleItem,
                'ui-input-group': InputGroup,
                'ui-input-group-prepend': InputGroupPrepend,
                'ui-input-group-append': InputGroupAppend,
            },
            directives: { tooltip() {} },
            mocks: {
                __: translate,
                __n: translateChoice,
                $number: new NumberFormatter(),
                $axios: { get: () => Promise.resolve({ data: [] }) },
            },
        },
    });
    await flushPromises();
}

test.each([
    [960, 1 / 5],
    [480, 2 / 5],
])('toolbar adapts to a %ipx selector and search fills the remainder', async (width, fraction) => {
    await render(width);

    const resource = wrapper.find('[role="combobox"]').element.getBoundingClientRect();
    const search = wrapper.findComponent({ ref: 'search' }).element.getBoundingClientRect();

    expect(resource.width).toBeCloseTo((width - 32) * fraction, 0);
    expect(search.width).toBeGreaterThan((width - 32) * 0.55);
    expect(search.right).toBeCloseTo(host.getBoundingClientRect().right - 16, 0);
});

test('ordinary selector keeps its existing search width without toolbar controls', async () => {
    await render(960, false);

    expect(wrapper.find('[role="combobox"]').exists()).toBe(false);
    expect(wrapper.findComponent({ ref: 'search' }).element.getBoundingClientRect().width).toBe(384);
});
