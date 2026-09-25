import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import CodeFieldtype from '@/components/fieldtypes/CodeFieldtype.vue';
import { CodeEditor, publishContextKey } from '@/components/ui';

window.Statamic = { $fieldActions: { get: () => [] } };
window.__ = (key) => key;
window.IntersectionObserver = class {
    observe() {}
    disconnect() {}
};

function mountField(code) {
    return mount(CodeFieldtype, {
        props: {
            value: { code, mode: 'javascript' },
            handle: 'code',
            config: { type: 'code', placeholder: 'Enter your code here' },
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
