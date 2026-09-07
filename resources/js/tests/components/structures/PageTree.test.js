import { mount, flushPromises } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import PageTree from '@/components/structures/PageTree.vue';
import Keys from '@/components/keys/Keys.js';

globalThis.__ = (key) => key;

let keys;
let outerSaves;
let outerBinding;

// jsdom reports an empty navigator.platform, so mousetrap resolves `mod` to `ctrl`.
function pressSave() {
    const event = new KeyboardEvent('keydown', {
        key: 's',
        code: 'KeyS',
        ctrlKey: true,
        bubbles: true,
        cancelable: true,
    });

    Object.defineProperty(event, 'keyCode', { get: () => 83 });
    Object.defineProperty(event, 'which', { get: () => 83 });

    document.dispatchEvent(event);
}

function mountTree(editable) {
    return mount(PageTree, {
        props: {
            pagesUrl: '/test/pages',
            editable,
        },
        global: {
            stubs: { Draggable: true, TreeBranch: true, PanelHeader: true, Panel: true, Icon: true },
            mocks: {
                $keys: keys,
                $config: { get: (key, fallback) => fallback },
                $axios: { get: () => Promise.resolve({ data: { pages: [] } }) },
            },
        },
    });
}

beforeEach(() => {
    keys = new Keys();
    outerSaves = 0;

    // Stand in for the publish form sitting behind the tree.
    outerBinding = keys.bindGlobal(['mod+s'], (e) => {
        e.preventDefault();
        outerSaves++;
    });
});

afterEach(() => outerBinding.destroy());

test('a read-only tree leaves the save shortcut alone', async () => {
    const wrapper = mountTree(false);
    await flushPromises();

    pressSave();
    expect(outerSaves).toBe(1);

    wrapper.unmount();
    await flushPromises();

    pressSave();
    expect(outerSaves).toBe(2);
});

test('an editable tree takes the save shortcut and gives it back', async () => {
    const wrapper = mountTree(true);
    await flushPromises();

    const save = vi.spyOn(wrapper.vm, 'save').mockImplementation(() => {});

    pressSave();
    expect(save).toHaveBeenCalledTimes(1);
    expect(outerSaves).toBe(0);

    wrapper.unmount();
    await flushPromises();

    pressSave();
    expect(save).toHaveBeenCalledTimes(1);
    expect(outerSaves).toBe(1);
});
