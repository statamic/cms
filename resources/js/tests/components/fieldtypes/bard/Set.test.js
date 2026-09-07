import { mount } from '@vue/test-utils';
import { afterEach, expect, test, vi } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import Set from '@/components/fieldtypes/bard/Set.vue';
import { containerContextKey } from '@/components/ui/Publish/Container.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.Statamic = { $fieldActions: { get: () => [] } };

function mountSet() {
    return mount(Set, {
        shallow: true,
        props: {
            editor: {},
            node: { attrs: { id: 'set-1', enabled: true, values: { type: 'my_set' } } },
            decorations: [],
            selected: false,
            extension: {
                options: {
                    bard: {
                        meta: { existing: {} },
                        collapsed: [],
                        setIndexes: { 'set-1': 0 },
                        config: { previews: false },
                        name: 'content',
                        handle: 'content',
                        fieldPathPrefix: null,
                        metaPathPrefix: null,
                        setHasError: () => false,
                    },
                },
            },
            getPos: () => 0,
            updateAttributes: vi.fn(),
            deleteNode: vi.fn(),
        },
        global: {
            stubs: { NodeViewWrapper: { template: '<div><slot /></div>' } },
            provide: {
                bard: { setConfigs: [{ handle: 'my_set', fields: [] }], isReadOnly: false, hasBeenFocused: false },
                bardSets: [],
                [containerContextKey]: {
                    values: { value: {} },
                    previews: { value: {} },
                    setFieldValue: vi.fn(),
                    setFieldMeta: vi.fn(),
                },
            },
        },
    });
}

function mockSelection({ containsSet }) {
    vi.spyOn(window, 'getSelection').mockReturnValue({
        rangeCount: 1,
        containsNode: () => containsSet,
    });
}

afterEach(() => vi.restoreAllMocks());

test('dragging from inside the set is prevented while the selection covers the whole set', async () => {
    const wrapper = mountSet();
    mockSelection({ containsSet: true });

    const event = new Event('dragstart', { bubbles: true, cancelable: true });
    wrapper.find('header').element.dispatchEvent(event);

    expect(event.defaultPrevented).toBe(true);
});

test('dragging from inside the set is allowed when the selection does not cover the whole set', async () => {
    const wrapper = mountSet();
    mockSelection({ containsSet: false });

    const event = new Event('dragstart', { bubbles: true, cancelable: true });
    wrapper.find('header').element.dispatchEvent(event);

    expect(event.defaultPrevented).toBe(false);
});

test('dragging a draggable element inside the set is allowed', async () => {
    const wrapper = mountSet();
    mockSelection({ containsSet: true });

    const header = wrapper.find('header').element;
    header.setAttribute('draggable', 'true');
    const event = new Event('dragstart', { bubbles: true, cancelable: true });
    header.dispatchEvent(event);

    expect(event.defaultPrevented).toBe(false);
});

test('clicking a custom select field does not bubble its mousedown to ProseMirror', () => {
    const wrapper = mountSet();
    const parent = document.createElement('div');
    const combobox = document.createElement('div');
    const selectedOption = document.createElement('div');
    const mousedown = vi.fn();

    combobox.dataset.uiCombobox = '';
    combobox.append(selectedOption);
    wrapper.find('[data-type]').element.append(combobox);
    parent.append(wrapper.element);
    parent.addEventListener('mousedown', mousedown);

    selectedOption.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));

    expect(mousedown).not.toHaveBeenCalled();
});
