import { mount, flushPromises } from '@vue/test-utils';
import { afterEach, expect, test, vi } from 'vitest';
import { ref } from 'vue';
import axios from 'axios';
import FieldInspector from '@/components/forms/builder/FieldInspector.vue';
import FieldConditionsBuilder from '@/components/field-conditions/Builder.vue';

vi.mock('axios');
vi.mock('@ui', async (importOriginal) => {
    const passthrough = { template: '<div><slot /></div>' };
    return { ...(await importOriginal()), PublishContainer: passthrough, PublishFieldsProvider: passthrough, PublishFields: passthrough };
});
vi.mock('@/util/debounce', () => ({ default: (fn) => Object.assign(fn, { cancel: () => {} }) }));

const pageProps = { fieldsets: {} };
vi.mock('@inertiajs/vue3', async (importOriginal) => ({ ...(await importOriginal()), usePage: () => ({ props: pageProps }) }));

const context = {};
vi.mock('@/pages/forms/Builder.vue', () => ({ injectBuilderContext: () => context }));

globalThis.__ = (key) => key;
globalThis.cp_url = (url) => url;
globalThis.Statamic = { $slug: { separatedBy: (separator) => ({ create: (value) => value.toLowerCase().replace(/[^a-z0-9]+/g, separator) }) } };

let wrapper;

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    pageProps.fieldsets = {};
});

const deferred = () => {
    let resolve;
    const promise = new Promise((r) => (resolve = r));
    return { promise, resolve };
};

const respondToEdit = (payload, originValues = {}) =>
    Promise.resolve({
        data: {
            fieldtype: {},
            blueprint: { tabs: [{ sections: [{ fields: [] }] }] },
            values: { ...payload.values },
            meta: {},
            originValues,
            originMeta: {},
        },
    });

const updateResponse = (payload) => ({ data: { values: { ...payload.values } } });

let updatePayloads = [];
const updateCalls = () => updatePayloads.map((payload) => ['/update', payload]);

const defaultFieldtypes = [
    { handle: 'radio', categories: ['choice'], collects_value: true },
    { handle: 'text', categories: ['text'], collects_value: true },
    { handle: 'heading', categories: ['information'], collects_value: false },
];

function setup({ field, fields = [], fieldtypes = defaultFieldtypes, originValues = {}, edit = respondToEdit, update = (payload) => Promise.resolve(updateResponse(payload)) }) {
    const beforeSave = [];
    const inspecting = ref(field);

    Object.assign(context, {
        dirty: vi.fn(),
        errors: ref({}),
        fieldtypes,
        form: { handle: 'contact' },
        inspecting,
        onBeforeSave: (callback) => {
            beforeSave.push(callback);
            return () => beforeSave.splice(beforeSave.indexOf(callback), 1);
        },
        pages: ref([{ sections: [{ fields: [...fields, field] }] }]),
        showFieldDirection: false,
        withoutDirtying: (callback) => callback(),
    });

    updatePayloads = [];
    axios.post.mockReset();
    axios.post.mockImplementation((url, payload) => {
        if (url.endsWith('/edit')) return edit(payload, originValues);

        updatePayloads.push(JSON.parse(JSON.stringify(payload)));
        return update(payload);
    });

    wrapper = mount(FieldInspector, {
        global: { stubs: { FieldValidationBuilder: true } },
    });

    return {
        inspecting,
        flushBeforeSave: () => Promise.all(beforeSave.map((callback) => callback())),
        beforeSave,
    };
}

const textField = (overrides = {}) => ({
    _id: 'phone-id',
    handle: 'phone',
    fieldtype: 'text',
    icon: 'text',
    type: 'inline',
    config: { type: 'text', display: 'Phone', width: 100, hidden: false },
    ...overrides,
});

const referenceField = (overrides = {}) =>
    textField({
        _id: 'company-id',
        handle: 'company',
        type: 'reference',
        field_reference: 'details.company',
        config: { type: 'text', display: 'Company', placeholder: 'Acme', width: 100, hidden: false },
        config_overrides: [],
        ...overrides,
    });

test('switching the condition key in the inspector drops the old key', async () => {
    const field = textField({ config: { type: 'text', display: 'Phone', if: { method: 'equals phone' } } });

    setup({
        field,
        fields: [{ _id: 'method-id', handle: 'method', fieldtype: 'radio', config: { type: 'radio', display: 'Method', options: { email: 'Email', phone: 'Phone' } } }],
    });
    await flushPromises();

    wrapper.findComponent(FieldConditionsBuilder).vm.when = 'unless';
    await flushPromises();

    expect(wrapper.vm.values).toMatchObject({ unless: { method: 'equals phone' } });
    expect(wrapper.vm.values).not.toHaveProperty('if');

    const update = updateCalls().at(-1)[1];
    expect(update.values).toMatchObject({ unless: { method: 'equals phone' } });
    expect(update.values).not.toHaveProperty('if');
    expect(field.config).not.toHaveProperty('if');
});

test('settings edits on a linked field are tracked as overrides', async () => {
    const field = referenceField({ config_overrides: ['instructions'] });

    setup({ field, originValues: { display: 'Company', placeholder: 'Acme', instructions: null } });
    await flushPromises();

    wrapper.vm.values.display = 'Employer';
    await flushPromises();

    expect(field.config_overrides).toEqual(['instructions', 'display']);
    expect(field.config.display).toBe('Employer');
});

test('values a fieldtype normalizes back to the fieldset value are not tracked as overrides', async () => {
    const field = referenceField();

    setup({ field, originValues: { display: 'Company', placeholder: 'Acme' } });
    await flushPromises();

    wrapper.vm.values.placeholder = 'Other';
    await flushPromises();
    wrapper.vm.values.placeholder = 'Acme';
    await flushPromises();

    expect(field.config_overrides).toEqual(['placeholder']);

    wrapper.vm.values.display = 'Company';
    await flushPromises();

    expect(field.config_overrides).toEqual(['placeholder']);
});

test('validation edits on a linked field are tracked as overrides', async () => {
    const field = referenceField();

    setup({ field, originValues: { display: 'Company' } });
    await flushPromises();

    wrapper.vm.values.validate = ['required'];
    await flushPromises();

    expect(field.config_overrides).toEqual(['validate']);
    expect(field.config.validate).toEqual(['required']);
});

test('overrides are not tracked on inline fields', async () => {
    const field = textField();

    setup({ field });
    await flushPromises();

    wrapper.vm.values.display = 'Mobile';
    await flushPromises();

    expect(field).not.toHaveProperty('config_overrides');
});

test('saving waits for a pending settings update', async () => {
    const field = textField();
    const response = deferred();

    const { flushBeforeSave } = setup({ field, update: () => response.promise });
    await flushPromises();

    wrapper.vm.values.display = 'Mobile';
    await flushPromises();

    let flushed = false;
    flushBeforeSave().then(() => (flushed = true));
    await flushPromises();

    expect(flushed).toBe(false);
    expect(field.config.display).toBe('Phone');

    response.resolve(updateResponse(updateCalls().at(-1)[1]));
    await flushPromises();

    expect(flushed).toBe(true);
    expect(field.config.display).toBe('Mobile');
});

test('saving waits for a new field to load', async () => {
    const field = textField({ handle: null, isNew: true });
    const response = deferred();

    const { flushBeforeSave } = setup({ field, edit: () => response.promise });
    await flushPromises();

    let flushed = false;
    flushBeforeSave().then(() => (flushed = true));
    await flushPromises();

    expect(flushed).toBe(false);

    response.resolve((await respondToEdit({ values: { ...field.config, handle: 'phone' } })));
    await flushPromises();

    expect(flushed).toBe(true);
    expect(field.handle).toBe('phone');
});

test('an edit is applied to its field when another field is selected before the update returns', async () => {
    const field = textField();
    const other = textField({ _id: 'email-id', handle: 'email', config: { type: 'text', display: 'Email' } });
    const response = deferred();

    const { inspecting } = setup({ field, fields: [other], update: () => response.promise });
    await flushPromises();

    wrapper.vm.values.display = 'Mobile';
    await flushPromises();

    inspecting.value = other;
    await flushPromises();

    response.resolve(updateResponse(updateCalls()[0][1]));
    await flushPromises();

    expect(field.config.display).toBe('Mobile');
    expect(other.config.display).toBe('Email');
});

test('returning to a field shows its own conditions, not the last field\'s', async () => {
    const method = { _id: 'method-id', handle: 'method', fieldtype: 'radio', config: { type: 'radio', display: 'Method', options: { email: 'Email', phone: 'Phone' } } };
    // Ids of their own, so the inspector's module-level cache from earlier tests doesn't cover them
    const field = textField({ _id: 'stale-phone-id', config: { type: 'text', display: 'Phone', if: { method: 'equals phone' } } });
    const other = textField({ _id: 'stale-email-id', handle: 'email', config: { type: 'text', display: 'Email' } });

    const { inspecting } = setup({ field, fields: [method, other] });
    await flushPromises();
    expect(wrapper.findComponent(FieldConditionsBuilder).vm.when).toBe('if');

    inspecting.value = other;
    await flushPromises();
    expect(wrapper.findComponent(FieldConditionsBuilder).vm.when).toBe('always');

    // The first field's settings are cached now, so the inspector doesn't reload; the builder must still start afresh
    inspecting.value = field;
    await flushPromises();
    expect(wrapper.findComponent(FieldConditionsBuilder).vm.when).toBe('if');
    expect(field.config.if).toEqual({ method: 'equals phone' });
});

test('an edit is applied to its field when the inspector closes before the update returns', async () => {
    const field = textField();
    const response = deferred();

    const { inspecting, flushBeforeSave } = setup({ field, update: () => response.promise });
    await flushPromises();

    wrapper.vm.values.display = 'Mobile';
    await flushPromises();

    inspecting.value = null;
    wrapper.unmount();
    wrapper = null;

    let flushed = false;
    flushBeforeSave().then(() => (flushed = true));
    await flushPromises();

    expect(flushed).toBe(false);

    response.resolve(updateResponse(updateCalls()[0][1]));
    await flushPromises();

    expect(flushed).toBe(true);
    expect(field.config.display).toBe('Mobile');
});

test('a new field gets a handle when another field is selected before its settings load', async () => {
    const field = textField({ handle: null, isNew: true, config: { type: 'text', display: 'Phone Number' } });
    const other = textField({ _id: 'email-id', handle: 'email', config: { type: 'text', display: 'Email' } });
    const response = deferred();
    let edits = 0;

    const { inspecting } = setup({ field, fields: [other], edit: (payload) => (edits++ === 0 ? response.promise : respondToEdit(payload)) });

    inspecting.value = other;
    await flushPromises();

    response.resolve(await respondToEdit({ values: { ...field.config, handle: null } }));
    await flushPromises();

    expect(field.handle).toBe('phone_number');
    expect(other.handle).toBe('email');
});

test('a stale update response does not overwrite a newer edit', async () => {
    const field = textField();
    const responses = [deferred(), deferred()];
    let call = 0;

    setup({ field, update: () => responses[call++].promise });
    await flushPromises();

    wrapper.vm.values.display = 'Mobile';
    await flushPromises();
    wrapper.vm.values.display = 'Mobile Phone';
    await flushPromises();

    responses[1].resolve(updateResponse(updateCalls()[1][1]));
    await flushPromises();
    responses[0].resolve(updateResponse(updateCalls()[0][1]));
    await flushPromises();

    expect(field.config.display).toBe('Mobile Phone');
});

test('a width change on the canvas survives a later settings edit', async () => {
    const field = referenceField();

    const { inspecting } = setup({ field, originValues: { display: 'Company' } });
    await flushPromises();

    inspecting.value.config.width = 50;
    await flushPromises();

    wrapper.vm.values.display = 'Employer';
    await flushPromises();

    expect(updateCalls().at(-1)[1].values).toMatchObject({ width: 50, display: 'Employer' });
    expect(field.config.width).toBe(50);
    expect(field.config_overrides).toEqual(expect.arrayContaining(['width', 'display']));
});

test('hiding a field on the canvas survives a later settings edit', async () => {
    const field = textField();

    const { inspecting } = setup({ field });
    await flushPromises();

    inspecting.value.config.hidden = true;
    await flushPromises();

    wrapper.vm.values.display = 'Mobile';
    await flushPromises();

    expect(updateCalls().at(-1)[1].values).toMatchObject({ hidden: true, display: 'Mobile' });
    expect(field.config.hidden).toBe(true);
});

test('fields from linked fieldsets are suggested for conditions with their prefix', async () => {
    pageProps.fieldsets = {
        details: {
            handle: 'details',
            fields: [
                { handle: 'company', type: 'inline', fieldtype: 'text', icon: 'text', config: { type: 'text', display: 'Company' } },
                { handle: 'intro', type: 'inline', fieldtype: 'heading', config: { type: 'heading', display: 'Intro' } },
            ],
        },
    };

    const field = textField();

    setup({ field, fields: [{ _id: 'import-id', type: 'import', fieldset: 'details', prefix: 'work_' }] });
    await flushPromises();

    expect(wrapper.vm.suggestableConditionFields).toEqual([
        { handle: 'work_company', config: { type: 'text', display: 'Company' }, icon: 'text' },
    ]);
});

test('hiding and showing a field on the canvas keeps its conditions', async () => {
    const field = textField({ config: { type: 'text', display: 'Phone', width: 100, if: { method: 'equals phone' } } });

    const { inspecting } = setup({
        field,
        fields: [{ _id: 'method-id', handle: 'method', fieldtype: 'radio', config: { type: 'radio', display: 'Method', options: { email: 'Email', phone: 'Phone' } } }],
    });
    await flushPromises();

    inspecting.value.config.hidden = true;
    await flushPromises();

    expect(field.config).toMatchObject({ hidden: true, if: { method: 'equals phone' } });
    expect(updateCalls().at(-1)[1].values).toMatchObject({ hidden: true, if: { method: 'equals phone' } });

    inspecting.value.config.hidden = false;
    await flushPromises();

    expect(field.config).toMatchObject({ if: { method: 'equals phone' } });
    expect(field.config.hidden).toBeFalsy();
});

test('hiding and showing a linked field on the canvas keeps its conditions as overrides', async () => {
    const field = referenceField({
        config: { type: 'text', display: 'Company', width: 100, unless: { method: 'equals phone' } },
        config_overrides: ['unless'],
    });

    const { inspecting } = setup({
        field,
        fields: [{ _id: 'method-id', handle: 'method', fieldtype: 'radio', config: { type: 'radio', display: 'Method', options: { email: 'Email', phone: 'Phone' } } }],
        originValues: { display: 'Company' },
    });
    await flushPromises();

    inspecting.value.config.hidden = true;
    await flushPromises();

    expect(field.config).toMatchObject({ hidden: true, unless: { method: 'equals phone' } });
    expect(field.config_overrides).toEqual(expect.arrayContaining(['hidden', 'unless']));

    inspecting.value.config.hidden = false;
    await flushPromises();

    expect(field.config).toMatchObject({ unless: { method: 'equals phone' } });
    expect(field.config_overrides).toContain('unless');
    expect(field.config_overrides).not.toContain('hidden');
});

test('fields are offered as conditions and get a validation tab based on whether their fieldtype collects a value', async () => {
    const field = textField({ handle: 'email', config: { type: 'text', display: 'Email' } });

    const { inspecting } = setup({
        field,
        fields: [
            { _id: 'address-id', handle: 'address', fieldtype: 'group', icon: 'group', config: { type: 'group', display: 'Address' } },
            { _id: 'gap-id', handle: 'gap', fieldtype: 'spacer', icon: 'spacer', config: { type: 'spacer', display: 'Gap' } },
            { _id: 'intro-id', handle: 'intro', fieldtype: 'heading', icon: 'heading', config: { type: 'heading', display: 'Intro' } },
        ],
        fieldtypes: [
            { handle: 'text', categories: ['text'], collects_value: true },
            { handle: 'group', categories: ['structure'], collects_value: true },
            { handle: 'spacer', categories: ['structure'], collects_value: false },
            { handle: 'heading', categories: ['information'], collects_value: false },
        ],
    });
    await flushPromises();

    expect(wrapper.vm.suggestableConditionFields.map((f) => f.handle)).toEqual(['address']);
    expect(wrapper.vm.shouldShowValidationTab).toBe(true);

    inspecting.value = context.pages.value[0].sections[0].fields[0];
    await flushPromises();
    expect(wrapper.vm.shouldShowValidationTab).toBe(true);

    inspecting.value = context.pages.value[0].sections[0].fields[1];
    await flushPromises();
    expect(wrapper.vm.shouldShowValidationTab).toBe(false);
});
