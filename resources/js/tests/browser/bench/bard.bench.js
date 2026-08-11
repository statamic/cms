import { bench, describe } from 'vitest';
import { mountBard } from '../helpers/mount.js';
import perf from '@/util/perf.js';

function createBardContext(profile, overrides) {
    let wrapper;
    let editor;
    let ready;

    return {
        async ensure() {
            if (!ready) {
                ready = (async () => {
                    perf.enable();
                    ({ wrapper } = await mountBard({
                        profile,
                        withSets: false,
                        overrides,
                    }));
                    editor = wrapper.vm.editor;
                    editor.commands.focus('end');
                    perf.reset();
                })();
            }

            await ready;

            return { wrapper, editor };
        },
        cleanup() {
            wrapper?.unmount();
            wrapper = null;
            editor = null;
            ready = null;
        },
    };
}

describe('bard keystroke latency', () => {
    const medium = createBardContext('medium', { paragraphs: 25, sets: 0, nestingDepth: 0 });
    const pathological = createBardContext('pathological', { paragraphs: 80, sets: 0, nestingDepth: 0 });

    bench(
        'insertContent character (medium)',
        async () => {
            const { editor } = await medium.ensure();
            perf.start('bench.bard.keystroke');
            editor.commands.insertContent('x');
            perf.stop('bench.bard.keystroke');
        },
        { iterations: 25, warmupIterations: 5 },
    );

    bench(
        'insertContent character (pathological)',
        async () => {
            const { editor } = await pathological.ensure();
            perf.start('bench.bard.keystroke.pathological');
            editor.commands.insertContent('x');
            perf.stop('bench.bard.keystroke.pathological');
        },
        { iterations: 15, warmupIterations: 3 },
    );
});
