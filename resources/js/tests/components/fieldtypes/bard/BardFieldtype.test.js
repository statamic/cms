import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { ref } from 'vue';
import { Editor } from '@tiptap/vue-3';
import * as Globals from '@/bootstrap/globals';
import BardFieldtype from '@/components/fieldtypes/bard/BardFieldtype.vue';
import Provider from '@/components/portals/Provider.vue';
import { containerContextKey } from '@/components/ui/Publish/Container.vue';
import { UPDATE_DEBOUNCE_MS } from '@/components/fieldtypes/constants.js';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.cp_url = (url) => url;
window.__ = (key, replacements = {}) =>
    Object.entries(replacements).reduce((string, [key, value]) => string.replace(`:${key}`, value), key);
window.__n = (key, count) => key.split('|')[count === 1 ? 0 : 1].replace(/:count/g, count);

// Five words, twenty four characters.
const value = [{ type: 'paragraph', content: [{ type: 'text', text: 'One two three four five.' }] }];

const sets = [{ handle: 'main', sets: [{ handle: 'card', display: 'Card', fields: [] }] }];

function set(id, label) {
    return { type: 'set', attrs: { id, enabled: true, values: { type: 'card', label } } };
}

function listItem(text) {
    return { type: 'listItem', content: [{ type: 'paragraph', ...(text && { content: [{ type: 'text', text }] }) }] };
}

async function mountField(config = {}, initialValue = value, container = {}) {
    const wrapper = mount(BardFieldtype, {
        props: {
            value: initialValue,
            handle: 'content',
            config: { sets: [], buttons: ['bold'], ...config },
            meta: { existing: {}, collapsed: [], defaults: {}, new: {} },
        },
        global: {
            stubs: {
                portal: {
                    components: { Provider },
                    props: ['provide'],
                    template: '<provider :variables="provide"><slot /></provider>',
                },
                'publish-field-fullscreen-header': true,
                'ui-icon': true,
                'set-picker': true,
            },
            mocks: {
                $bard: { extensionCallbacks: [], extensionReplacementCallbacks: [], buttonCallbacks: [] },
                $events: { $on: () => {}, $off: () => {} },
            },
            provide: {
                [containerContextKey]: {
                    values: { value: {} },
                    previews: { value: {} },
                    errors: { value: {} },
                    setFieldValue: vi.fn(),
                    setFieldMeta: vi.fn(),
                    ...container,
                },
            },
        },
    });

    await vi.waitUntil(() => wrapper.vm.editor);
    await wrapper.vm.$nextTick();

    return wrapper;
}

async function mountFieldInContainer(config, initialValue) {
    const values = ref({ content: initialValue });
    const wrapper = await mountField(config, initialValue, { values });

    await wrapper.setProps({
        'onUpdate:value': (value) => {
            data_set(values.value, 'content', value);
            wrapper.setProps({ value });
        },
    });

    return { wrapper, values };
}

async function type(wrapper, text) {
    wrapper.vm.editor.commands.insertContent(text);
    await wrapper.vm.$nextTick();
}

function focusEditor(wrapper) {
    vi.spyOn(document, 'activeElement', 'get').mockReturnValue(wrapper.vm.editor.view.dom);
}

beforeEach(() => {
    window.Statamic = {
        $components: { has: () => true, register: () => {} },
        $fieldActions: { get: () => [] },
        $commandPalette: { preventIf: () => {}, add: () => {} },
        $config: { get: () => null },
    };
});

afterEach(() => vi.restoreAllMocks());

// Serializing the whole document is expensive, and the reading time is the only thing that needs it.
test('the document is not serialized to html on mount when reading time is disabled', async () => {
    const getHTML = vi.spyOn(Editor.prototype, 'getHTML');

    const wrapper = await mountField({ reading_time: false });

    expect(getHTML).not.toHaveBeenCalled();
    expect(wrapper.vm.html).toBe(null);
});

test('the document is serialized to html on mount when reading time is enabled', async () => {
    const wrapper = await mountField({ reading_time: true });

    expect(wrapper.vm.html).toContain('One two three four five.');
});

test('the document is not serialized to html on update when reading time is disabled', async () => {
    const wrapper = await mountField({ reading_time: false });
    const getHTML = vi.spyOn(wrapper.vm.editor, 'getHTML');

    await type(wrapper, ' six seven eight.');

    expect(getHTML).not.toHaveBeenCalled();
});

test('the document is serialized to html on update when reading time is enabled', async () => {
    const wrapper = await mountField({ reading_time: true });
    const getHTML = vi.spyOn(wrapper.vm.editor, 'getHTML');

    await type(wrapper, ' six seven eight.');

    expect(getHTML).toHaveBeenCalled();
    expect(wrapper.vm.html).toContain('six seven eight.');
});

test('reading time is rendered when enabled', async () => {
    const wrapper = await mountField({ reading_time: true });

    await type(wrapper, ' six seven eight.');

    expect(wrapper.vm.readingTime).toMatch(/^\d{2}:\d{2}$/);
    expect(wrapper.find('.bard-footer-toolbar').text()).toContain(wrapper.vm.readingTime);
});

// The counts come from the character count extension's storage rather than the serialized
// html, so they are rendered whether or not the html is being kept up to date.
test('word and character counts are rendered when reading time is disabled', async () => {
    const wrapper = await mountField({ reading_time: false, word_count: true, character_limit: 100 });

    expect(wrapper.find('.bard-footer-toolbar').text()).toBe('5 words, 24/100 characters');
});

// After saving with a keyboard shortcut the editor keeps focus, and the server may have
// removed empty nodes. The editor has to follow that, otherwise the sets it renders
// point at the wrong indexes in the saved value.
// @see https://github.com/statamic/cms/issues/15440
test('the editor is rebuilt when the value loses nodes while the editor is focused', async () => {
    const wrapper = await mountField({ sets }, [
        { type: 'paragraph' },
        set('set-a', 'A'),
        { type: 'paragraph' },
        set('set-b', 'B'),
    ]);
    focusEditor(wrapper);

    await wrapper.setProps({ value: [set('set-a', 'A'), set('set-b', 'B')] });

    expect(wrapper.vm.editor.getJSON().content).toEqual([set('set-a', 'A'), set('set-b', 'B')]);
});

// Typing inside a set's fields changes the value from within the editor. Rebuilding the
// editor for that would destroy the field being typed in.
test('the editor is left alone when only set values change while the editor is focused', async () => {
    const wrapper = await mountField({ sets }, [set('set-a', 'A'), { type: 'paragraph' }]);
    focusEditor(wrapper);
    const doc = wrapper.vm.editor.state.doc;

    await wrapper.setProps({ value: [set('set-a', 'Changed'), { type: 'paragraph' }] });

    expect(wrapper.vm.editor.state.doc).toBe(doc);
});

test('the editor is left alone when only set values change and the value omits attribute defaults', async () => {
    const wrapper = await mountField({ sets }, [set('set-a', 'A'), { type: 'paragraph' }]);
    focusEditor(wrapper);
    const doc = wrapper.vm.editor.state.doc;

    await wrapper.setProps({
        value: [{ type: 'set', attrs: { id: 'set-a', values: { type: 'card', label: 'Changed' } } }, { type: 'paragraph' }],
    });

    expect(wrapper.vm.editor.state.doc).toBe(doc);
});

test('the editor is left alone when the value only differs by omitted attribute defaults', async () => {
    const wrapper = await mountField({ sets }, [set('set-a', 'A'), { type: 'paragraph' }]);
    const doc = wrapper.vm.editor.state.doc;

    await wrapper.setProps({
        value: [{ type: 'set', attrs: { id: 'set-a', values: { type: 'card', label: 'A' } } }, { type: 'paragraph' }],
    });

    expect(wrapper.vm.editor.state.doc).toBe(doc);
});

test('the editor is rebuilt when the value changes while the editor is not focused', async () => {
    const wrapper = await mountField({ sets }, [set('set-a', 'A'), { type: 'paragraph' }]);

    await wrapper.setProps({ value: [set('set-a', 'Changed'), { type: 'paragraph' }] });

    expect(wrapper.vm.editor.getJSON().content).toEqual([set('set-a', 'Changed'), { type: 'paragraph' }]);
});

test('the editor is rebuilt when the value omits attribute defaults but changes content', async () => {
    const wrapper = await mountField({ sets }, [set('set-a', 'A'), { type: 'paragraph' }]);

    await wrapper.setProps({
        value: [{ type: 'set', attrs: { id: 'set-a', values: { type: 'card', label: 'Changed' } } }, { type: 'paragraph' }],
    });

    expect(wrapper.vm.editor.getJSON().content).toEqual([set('set-a', 'Changed'), { type: 'paragraph' }]);
});

test('the editor is cleared when an empty value arrives', async () => {
    const wrapper = await mountField({ sets }, [{ type: 'paragraph' }, { type: 'paragraph' }]);

    await wrapper.setProps({ value: [] });

    expect(wrapper.vm.editor.getJSON().content).toEqual([{ type: 'paragraph' }]);
});

test('sets keep their values when an edit moves them without changing the node count', async () => {
    const { wrapper, values } = await mountFieldInContainer({ sets, buttons: ['unorderedlist'] }, [
        { type: 'bulletList', content: [listItem('One'), listItem(), listItem('Two')] },
        set('set-a', 'A'),
        { type: 'paragraph', content: [{ type: 'text', text: 'Between' }] },
        set('set-b', 'B'),
    ]);
    const editor = wrapper.vm.editor;

    editor.state.doc.descendants((node, pos) => {
        if (node.type.name === 'paragraph' && node.childCount === 0) editor.commands.setTextSelection(pos + 1);
    });
    editor.commands.keyboardShortcut('Enter');
    await new Promise((resolve) => setTimeout(resolve, UPDATE_DEBOUNCE_MS + 50));

    const labels = (nodes) => nodes.filter((node) => node.type === 'set').map((node) => node.attrs.values.label);
    expect(editor.getJSON().content.map((node) => node.type)).toEqual(['bulletList', 'paragraph', 'bulletList', 'set', 'paragraph', 'set']);
    expect(labels(editor.getJSON().content)).toEqual(['A', 'B']);
    expect(labels(values.value.content)).toEqual(['A', 'B']);
});

test('typing is still debounced when it does not move any sets', async () => {
    const { wrapper, values } = await mountFieldInContainer({ sets }, [
        { type: 'paragraph', content: [{ type: 'text', text: 'One' }] },
        set('set-a', 'A'),
    ]);

    wrapper.vm.editor.commands.insertContentAt(4, ' two');
    await wrapper.vm.$nextTick();

    expect(values.value.content[0].content[0].text).toBe('One');

    await new Promise((resolve) => setTimeout(resolve, UPDATE_DEBOUNCE_MS + 50));

    expect(values.value.content[0].content[0].text).toBe('One two');
});
