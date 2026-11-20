import { mount, flushPromises } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { reactive } from 'vue';
import LogicList from '@/components/forms/logic/LogicList.vue';
import FieldLogic from '@/components/forms/logic/FieldLogic.vue';
import Builder from '@/components/field-conditions/Builder.vue';
import Condition from '@/components/field-conditions/Condition.vue';

globalThis.__ = (key) => key;

const makePages = (phoneConfig = {}, extraFields = []) =>
    reactive([
        {
            sections: [
                {
                    fields: [
                        { handle: 'method', fieldtype: 'radio', config: { display: 'Method', options: { email: 'Email', phone: 'Phone' } } },
                        { handle: 'phone', fieldtype: 'text', config: { display: 'Phone', ...phoneConfig } },
                        ...extraFields,
                    ],
                },
            ],
        },
    ]);

const suggestableFields = [
    { handle: 'method', pageIndex: 0, config: { type: 'radio', display: 'Method', options: { email: 'Email', phone: 'Phone' } } },
    { handle: 'name', pageIndex: 0, config: { type: 'text', display: 'Name' } },
];

const mountList = async (pages) => {
    const wrapper = mount(LogicList, {
        props: { pages, suggestableFields, fieldtypes: [] },
        global: { stubs: { teleport: true } },
    });
    await flushPromises();
    return wrapper;
};

const phoneConfig = (pages) => pages[0].sections[0].fields[1].config;
const builders = (wrapper) => wrapper.findAllComponents(Builder);

test('switching a rule between show and hide keeps its conditions', async () => {
    const pages = makePages({ if: { method: 'equals phone' } });
    const wrapper = await mountList(pages);

    builders(wrapper)[0].vm.when = 'unless';
    await flushPromises();

    expect(phoneConfig(pages)).toMatchObject({ unless: { method: 'equals phone' } });
    expect(phoneConfig(pages)).not.toHaveProperty('if');
    expect(builders(wrapper)).toHaveLength(1);
});

test('switching a rule from always hide to show when clears hidden', async () => {
    const pages = makePages({ hidden: true });
    const wrapper = await mountList(pages);

    builders(wrapper)[0].vm.when = 'if';
    await flushPromises();

    expect(phoneConfig(pages)).not.toHaveProperty('hidden');
    expect(builders(wrapper)).toHaveLength(1);
});

test('a new rule stays while its first condition is incomplete', async () => {
    const pages = makePages();
    const wrapper = await mountList(pages);

    wrapper.findComponent(FieldLogic).vm.addCondition('phone');
    await flushPromises();

    expect(builders(wrapper)).toHaveLength(1);

    wrapper.findComponent(Condition).vm.$emit('update:condition', { field: 'method', operator: 'equals', value: null });
    await flushPromises();

    expect(builders(wrapper)).toHaveLength(1);

    wrapper.findComponent(Condition).vm.$emit('update:condition', { field: 'method', operator: 'equals', value: 'phone' });
    await flushPromises();

    expect(phoneConfig(pages)).toMatchObject({ if: { method: 'equals phone' } });
});

test('deleting an always hide rule removes it', async () => {
    const pages = makePages({ hidden: true });
    const wrapper = await mountList(pages);

    wrapper.findComponent(FieldLogic).vm.removeCondition('phone');
    await flushPromises();

    expect(phoneConfig(pages)).not.toHaveProperty('hidden');
    expect(builders(wrapper)).toHaveLength(0);
});

test.each([
    ['show_when', 'if'],
    ['show_when_any', 'if_any'],
    ['hide_when', 'unless'],
    ['hide_when_any', 'unless_any'],
])('legacy %s conditions are shown and replaced when edited', async (legacyKey, key) => {
    const pages = makePages({ [legacyKey]: { method: 'equals phone' } });
    const wrapper = await mountList(pages);

    const builder = builders(wrapper)[0];
    expect(builder.vm.conditions[0]).toMatchObject({ field: 'method', value: 'phone' });

    builder.vm.conditions[0].value = 'email';
    await flushPromises();

    expect(phoneConfig(pages)).toMatchObject({ [key]: { method: 'equals email' } });
    expect(phoneConfig(pages)).not.toHaveProperty(legacyKey);
});

test('legacy conditions on untouched fields are left alone', async () => {
    const pages = makePages({ if: { method: 'equals phone' } }, [
        { handle: 'fax', fieldtype: 'text', config: { display: 'Fax', show_when: { method: 'equals email' } } },
    ]);
    const wrapper = await mountList(pages);

    builders(wrapper)[0].vm.conditions[0].value = 'email';
    await flushPromises();

    expect(pages[0].sections[0].fields[2].config).toMatchObject({ show_when: { method: 'equals email' } });
    expect(pages[0].sections[0].fields[2].config).not.toHaveProperty('if');
});
