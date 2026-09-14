import { test, expect, beforeEach, afterEach } from 'vitest';
import { ref, reactive } from 'vue';
import toFieldActions from '../components/field-actions/toFieldActions.js';

let originalStatamic;

beforeEach(() => {
    originalStatamic = globalThis.Statamic;
    globalThis.Statamic = {
        $fieldActions: {
            get: () => [
                { title: 'Quick', quick: true, run: () => {} },
                { title: 'Slow', quick: false, run: () => {} },
                { title: 'Hidden', quick: false, visible: false, run: () => {} },
            ],
        },
    };
});

afterEach(() => {
    globalThis.Statamic = originalStatamic;
});

test('returns FieldAction instances safe for reactive containers (ref)', () => {
    const actions = toFieldActions('some-binding', {});
    const r = ref(actions);

    // Without markRaw, accessing a getter that reads a private field
    // throws "can't access private field or method: object is not the right class".
    expect(() => r.value[0].quick).not.toThrow();
    expect(r.value[0].quick).toBe(true);
    expect(r.value[1].quick).toBe(false);
});

test('returns FieldAction instances safe for reactive containers (reactive)', () => {
    const actions = toFieldActions('some-binding', {});
    const state = reactive({ list: actions });

    expect(() => state.list[0].quick).not.toThrow();
    expect(state.list.filter((a) => !a.quick).map((a) => a.title)).toEqual(['Slow']);
});

test('filters out non-visible actions', () => {
    const actions = toFieldActions('some-binding', {});
    expect(actions.map((a) => a.title)).toEqual(['Quick', 'Slow']);
});
