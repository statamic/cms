import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import LinkToolbar from '@/components/fieldtypes/bard/LinkToolbar.vue';

window.__ = (key) => key;

// The toolbar's autofocus() schedules a setTimeout that calls this.$refs.urlInput.focus().
// Under a shallow mount that ref doesn't exist, so use fake timers to keep the leaked
// timer from firing after the test finishes (which would throw an unhandled error).
beforeEach(() => vi.useFakeTimers());
afterEach(() => vi.useRealTimers());

function makeBard({ linkTypes = {}, linkData = {} } = {}) {
    return {
        meta: { linkTypes, linkData },
        editor: {
            view: { state: { selection: { from: 0, to: 0 } } },
            state: { doc: { textBetween: () => '' } },
        },
        events: { on: vi.fn(), off: vi.fn() },
        updateMeta: vi.fn(),
    };
}

function mountToolbar({ linkTypes = {}, linkAttrs = {}, config = {} } = {}) {
    const bard = makeBard({ linkTypes });

    const wrapper = mount(LinkToolbar, {
        shallow: true,
        props: { bard, config, linkAttrs },
    });

    return { wrapper, bard };
}

test('linkTypes orders url first, registered types in the middle, and mailto/tel last', () => {
    const { wrapper } = mountToolbar({
        linkTypes: {
            entry: { title: 'Entry', component: 'relationship', config: {}, meta: {} },
            custom: { title: 'Custom Type', component: 'custom', config: {}, meta: {} },
        },
    });

    expect(wrapper.vm.linkTypes).toEqual([
        { type: 'url', title: 'URL' },
        { type: 'entry', title: 'Entry' },
        { type: 'custom', title: 'Custom Type' },
        { type: 'mailto', title: 'Email' },
        { type: 'tel', title: 'Phone' },
    ]);
});

test('registeredLinkType is null for the built-in url type', () => {
    const { wrapper } = mountToolbar();

    expect(wrapper.vm.linkType).toBe('url');
    expect(wrapper.vm.registeredLinkType).toBeNull();
});

test('registeredLinkType resolves the matching type from bard meta, and its fieldtype component name', () => {
    const { wrapper } = mountToolbar({
        linkTypes: {
            entry: { title: 'Entry', component: 'relationship', config: { foo: 'bar' }, meta: {} },
        },
    });

    wrapper.vm.linkType = 'entry';

    expect(wrapper.vm.registeredLinkType).toEqual({
        title: 'Entry',
        component: 'relationship',
        config: { foo: 'bar' },
        meta: {},
    });
    expect(wrapper.vm.registeredLinkTypeComponent).toBe('relationship-fieldtype');
});

test('typeSelected sets the url to a statamic:// reference for the current link type', () => {
    const { wrapper } = mountToolbar({
        linkTypes: {
            entry: { title: 'Entry', component: 'relationship', config: {}, meta: {} },
        },
    });

    wrapper.vm.linkType = 'entry';
    wrapper.vm.typeSelected(['42']);

    expect(wrapper.vm.url.entry).toBe('statamic://entry::42');
    expect(wrapper.vm.canCommit).toBe(true);
});

test('typeSelected clears the url when nothing is selected', () => {
    const { wrapper } = mountToolbar({
        linkTypes: {
            entry: { title: 'Entry', component: 'relationship', config: {}, meta: {} },
        },
    });

    wrapper.vm.linkType = 'entry';
    wrapper.vm.typeSelected(['42']);
    wrapper.vm.typeSelected([]);

    expect(wrapper.vm.url.entry).toBeNull();
});

test.each([
    ['url', true],
    ['entry', true],
    ['custom', true],
    ['mailto', false],
    ['tel', false],
])('canHaveTarget for link type "%s" is %s', async (linkType, expected) => {
    const { wrapper } = mountToolbar({
        linkTypes: {
            entry: { title: 'Entry', component: 'relationship', config: {}, meta: {} },
            custom: { title: 'Custom', component: 'custom', config: {}, meta: {} },
        },
    });

    wrapper.vm.linkType = linkType;
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.canHaveTarget).toBe(expected);
});
