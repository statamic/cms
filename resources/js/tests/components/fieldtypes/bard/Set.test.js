import { mount } from '@vue/test-utils';
import { afterEach, expect, test, vi } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import Set from '@/components/fieldtypes/bard/Set.vue';
import { containerContextKey } from '@/components/ui/Publish/Container.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.Statamic = { $fieldActions: { get: () => [] } };

function mountSet({ attachTo, bard } = {}) {
    return mount(Set, {
        shallow: true,
        attachTo,
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
                        ...bard,
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
                    visibleValues: { value: {} },
                    revealerValues: { value: {} },
                    hiddenFields: { value: {} },
                    setHiddenField: vi.fn(),
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

function startDraggingSet() {
    const root = document.createElement('div');
    root.classList.add('bard-fieldtype');
    document.body.appendChild(root);

    const wrapper = mountSet({ attachTo: root, bard: { dragging: false, collapseAll: vi.fn() } });
    const bard = wrapper.props('extension').options.bard;

    wrapper.find('[data-drag-handle]').element.dispatchEvent(new Event('mousedown', { bubbles: true }));
    wrapper.element.dispatchEvent(new Event('dragstart', { bubbles: true }));

    return { root, bard, wrapper };
}

test('dragging a set hides the set bodies without collapsing the sets', () => {
    const { root, bard } = startDraggingSet();

    expect(root.classList.contains('bard-dragging')).toBe(true);
    expect(bard.dragging).toBe(true);
    expect(bard.collapseAll).not.toHaveBeenCalled();
});

test('the dragging class is removed even when the drop recreates the set', () => {
    const { root, wrapper } = startDraggingSet();

    // The drop swaps in a new node view, so dragend fires on an element that's no longer
    // in the document and never reaches the listener on it.
    const element = wrapper.element;
    element.remove();
    element.dispatchEvent(new Event('dragend', { bubbles: true }));

    expect(root.classList.contains('bard-dragging')).toBe(false);
});
