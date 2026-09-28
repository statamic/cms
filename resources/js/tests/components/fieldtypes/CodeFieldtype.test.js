import { mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import CodeFieldtype from '@/components/fieldtypes/CodeFieldtype.vue';
import { CodeEditor, publishContextKey } from '@/components/ui';

window.Statamic = { $fieldActions: { get: () => [] } };
window.__ = (key) => key;
window.IntersectionObserver = class {
    observe() {}
    disconnect() {}
};

function mountField(code, config = {}, mode = 'javascript') {
    return mount(CodeFieldtype, {
        props: {
            value: { code, mode },
            handle: 'code',
            config: { type: 'code', placeholder: 'Enter your code here', ...config },
        },
        global: {
            provide: { [publishContextKey]: {} },
            stubs: {
                portal: { template: '<div><slot /></div>' },
                'publish-field-fullscreen-header': true,
            },
        },
    });
}

test('shows the configured placeholder when the editor is empty', async () => {
    const wrapper = mountField('');
    await wrapper.vm.$nextTick();

    expect(wrapper.get('.CodeMirror-placeholder').text()).toBe('Enter your code here');
});

test('does not show the placeholder when the editor has code', async () => {
    const wrapper = mountField('const answer = 42;');
    await wrapper.vm.$nextTick();

    expect(wrapper.find('.CodeMirror-placeholder').exists()).toBe(false);
    expect(wrapper.getComponent(CodeEditor).props('modelValue')).toBe('const answer = 42;');
});

test('emits edited code with the current language', async () => {
    const wrapper = mountField('');
    await wrapper.vm.$nextTick();

    vi.useFakeTimers();
    try {
        wrapper.get('.CodeMirror').element.CodeMirror.setValue('const answer = 42;');

        expect(wrapper.emitted('update:value')).toBeUndefined();
        vi.advanceTimersByTime(150);
        expect(wrapper.emitted('update:value')).toEqual([[{ code: 'const answer = 42;', mode: 'javascript' }]]);
    } finally {
        vi.useRealTimers();
        wrapper.unmount();
    }
});

test('uses the configured language unless the value specifies one', () => {
    const configured = mountField('', { mode: 'php' }, null);
    const selected = mountField('', { mode: 'php' }, 'javascript');

    expect(configured.getComponent(CodeEditor).props('mode')).toBe('php');
    expect(selected.getComponent(CodeEditor).props('mode')).toBe('javascript');
});

test('uses the configured color mode for the editor theme', async () => {
    const wrapper = mountField('', { color_mode: 'dark' });
    await wrapper.vm.$nextTick();

    expect(wrapper.get('.theme-dark').exists()).toBe(true);
    expect(wrapper.get('.CodeMirror').classes()).toContain('cm-s-material');
});
