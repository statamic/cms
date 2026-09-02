import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import AutocompleteEditor from '@/components/ui/AutocompleteEditor/AutocompleteEditor.vue';

window.__ = (key) => key;

const sampleOptions = [
    { value: 'jason', label: 'Jason Varga' },
    { value: 'jesse', label: 'Jesse Leite' },
];

async function mountEditor(props = {}) {
    const wrapper = mount(AutocompleteEditor, {
        props: { modelValue: [], options: sampleOptions, ...props },
    });

    // useEditor() initializes the tiptap instance asynchronously, so the
    // ProseMirror DOM isn't guaranteed to exist after a single tick.
    await wrapper.vm.$nextTick();
    await wrapper.vm.$nextTick();

    return wrapper;
}

function pressEnter(dom) {
    dom.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true, cancelable: true }));
}

test('renders a toolbar button for each configured button', async () => {
    const wrapper = await mountEditor({ buttons: ['bold', 'h2'] });

    const labels = wrapper.findAll('button').map((button) => button.attributes('aria-label'));

    expect(labels).toEqual(['Bold', 'Heading 2']);
});

test('unknown button keys are ignored', async () => {
    const wrapper = await mountEditor({ buttons: ['bold', 'not-a-real-button'] });

    const labels = wrapper.findAll('button').map((button) => button.attributes('aria-label'));

    expect(labels).toEqual(['Bold']);
});

test('inline mode hides the toolbar', async () => {
    const wrapper = await mountEditor({ inline: true, buttons: ['bold', 'italic'] });

    expect(wrapper.findAll('button')).toHaveLength(0);
});

test('enableLineBreaks false blocks Enter in inline mode', async () => {
    const wrapper = await mountEditor({ inline: true, enableLineBreaks: false });
    const { editor } = wrapper.vm;

    editor.commands.insertContent('hello');
    editor.commands.focus('end');
    pressEnter(wrapper.find('.ProseMirror').element);
    await wrapper.vm.$nextTick();

    expect(editor.getJSON().content).toEqual([
        { type: 'paragraph', content: [{ type: 'text', text: 'hello' }] },
    ]);
});

test('enableLineBreaks true inserts a hard break in inline mode', async () => {
    const wrapper = await mountEditor({ inline: true, enableLineBreaks: true });
    const { editor } = wrapper.vm;

    editor.commands.insertContent('hello');
    editor.commands.focus('end');
    pressEnter(wrapper.find('.ProseMirror').element);
    await wrapper.vm.$nextTick();

    expect(editor.getJSON().content).toEqual([
        {
            type: 'paragraph',
            content: [{ type: 'text', text: 'hello' }, { type: 'hardBreak' }],
        },
    ]);
});

test('enableLineBreaks false still splits into a new paragraph in block mode', async () => {
    const wrapper = await mountEditor({ inline: false, enableLineBreaks: false });
    const { editor } = wrapper.vm;

    editor.commands.insertContent('hello');
    editor.commands.focus('end');
    pressEnter(wrapper.find('.ProseMirror').element);
    await wrapper.vm.$nextTick();

    expect(editor.getJSON().content).toEqual([
        { type: 'paragraph', content: [{ type: 'text', text: 'hello' }] },
        { type: 'paragraph' },
    ]);
});

test('enableLineBreaks true splits into a new paragraph in block mode', async () => {
    const wrapper = await mountEditor({ inline: false, enableLineBreaks: true });
    const { editor } = wrapper.vm;

    editor.commands.insertContent('hello');
    editor.commands.focus('end');
    pressEnter(wrapper.find('.ProseMirror').element);
    await wrapper.vm.$nextTick();

    expect(editor.getJSON().content).toEqual([
        { type: 'paragraph', content: [{ type: 'text', text: 'hello' }] },
        { type: 'paragraph' },
    ]);
});

test('inserting a mention produces a node with value and label attrs', async () => {
    const wrapper = await mountEditor();
    const { editor } = wrapper.vm;

    editor.commands.insertContent({ type: 'mention', attrs: { value: 'jesse', label: 'Jesse Leite' } });

    expect(editor.getJSON().content).toEqual([
        {
            type: 'paragraph',
            content: [{ type: 'mention', attrs: { value: 'jesse', label: 'Jesse Leite' } }],
        },
    ]);
});

test('update:modelValue emits the ProseMirror content array', async () => {
    const wrapper = await mountEditor();
    const { editor } = wrapper.vm;

    editor.commands.insertContent('hello');
    await wrapper.vm.$nextTick();

    const emitted = wrapper.emitted('update:modelValue');

    expect(emitted).toBeTruthy();
    expect(emitted.at(-1)[0]).toEqual([
        { type: 'paragraph', content: [{ type: 'text', text: 'hello' }] },
    ]);
});

test('suggestion items are filtered by query against the option label', async () => {
    const wrapper = await mountEditor();
    const { editor } = wrapper.vm;

    const mention = editor.extensionManager.extensions.find((extension) => extension.name === 'mention');

    expect(mention.options.suggestion.items({ query: 'jes' })).toEqual([
        { value: 'jesse', label: 'Jesse Leite' },
    ]);
    expect(mention.options.suggestion.items({ query: '' })).toEqual(sampleOptions);
    expect(mention.options.suggestion.items({ query: 'zzz' })).toEqual([]);
});
