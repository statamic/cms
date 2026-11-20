import { flushPromises, mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import FieldConditions from '@/components/FieldConditions';
import Settings from '@/components/fields/Settings.vue';
import HiddenFieldtype from '@/components/fieldtypes/HiddenFieldtype.vue';
import PublishContainer from '@/components/ui/Publish/Container.vue';

vi.mock('@inertiajs/vue3', () => ({ usePage: () => ({ props: { extensionRules: [] } }) }));

Object.assign(window, Globals);

window.Statamic = {
    $app: { component: () => HiddenFieldtype },
    $conditions: new FieldConditions(),
    $config: { get: (key) => key === 'sites' ? [] : {} },
    $dirty: { has: () => false, add: () => {}, remove: () => {} },
    $events: { $emit: () => {} },
};

const mountSettings = (values, blueprint, stubs = {}) => mount(Settings, {
    props: { config: { handle: 'example' }, type: 'text', fields: [], suggestableConditionFields: [] },
    global: {
        components: { 'ui-publish-container': PublishContainer, 'hidden-fieldtype': HiddenFieldtype },
        stubs: {
            StackHeader: { template: '<header><slot name="actions" /></header>' },
            StackContent: { template: '<main><slot /></main>' },
            ElementContainer: {
                template: '<div><slot /></div>',
                mounted() { this.$emit('resized', { width: 1000 }); },
            },
            ...stubs,
        },
        mocks: {
            $config: { get: () => '12.0.0' },
            $keys: { bindGlobal: () => ({ destroy: () => {} }) },
            $axios: {
                post: async (url, payload) => ({
                    data: url.endsWith('fields/edit')
                        ? { values, blueprint, meta: {}, fieldtype: { icon: 'text' } }
                        : payload.values,
                }),
            },
        },
    },
});

const apply = async (wrapper) => {
    await wrapper.findAll('button').find(button => button.text() === 'Apply').trigger('click');
    await flushPromises();
};

test.each([
    [true, 'Settings'],
    [false, 'Settings'],
    [false, 'Conditions'],
    [false, 'Validation'],
])('applying field settings respects conditions when the toggle is %s from the %s tab', async (enabled, tab) => {
    const values = { handle: 'example', enabled, amount: 12, retained: 34, validate: ['required'] };
    const blueprint = {
        tabs: [{
            handle: 'main',
            sections: [{
                fields: [
                    { handle: 'enabled', type: 'hidden' },
                    { handle: 'amount', type: 'hidden', if: { enabled: 'equals true' } },
                    { handle: 'retained', type: 'hidden', if: { enabled: 'equals true' }, always_save: true },
                ],
            }],
        }],
    };

    const wrapper = mountSettings(values, blueprint, { FieldConditionsBuilder: true, FieldValidationBuilder: true });

    await flushPromises();

    const activeTab = wrapper.findAll('[role="tab"]').find(button => button.text() === tab);
    await activeTab.trigger('mousedown', { button: 0 });
    expect(activeTab.attributes('aria-selected')).toBe('true');
    expect(wrapper.findAll('[role="tabpanel"]')[0].classes('hidden')).toBe(tab !== 'Settings');

    await apply(wrapper);

    expect(wrapper.emitted('committed')[0][0]).toEqual({
        handle: 'example',
        enabled,
        ...(enabled ? { amount: 12 } : {}),
        retained: 34,
        validate: ['required'],
    });

    wrapper.unmount();
});

test('applying untouched field settings does not mark validation or condition settings as edited', async () => {
    const values = { handle: 'example', validate: ['required'], always_save: true, if: { other: 'equals foo' } };
    const blueprint = { tabs: [{ handle: 'main', sections: [{ fields: [{ handle: 'handle', type: 'hidden' }] }] }] };

    const wrapper = mountSettings(values, blueprint, { SortableList: true });

    await flushPromises();
    await apply(wrapper);

    expect(wrapper.emitted('committed')[0][1]).toEqual([]);

    wrapper.unmount();
});
