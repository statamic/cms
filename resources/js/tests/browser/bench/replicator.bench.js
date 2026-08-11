import { bench, describe } from 'vitest';
import { nextTick } from 'vue';
import { mountReplicator } from '../helpers/mount.js';
import perf from '@/util/perf.js';

function createReplicatorContext(profile = 'medium') {
    let wrapper;
    let vm;
    let ready;

    return {
        async ensure() {
            if (!ready) {
                ready = (async () => {
                    perf.enable();
                    // Shallow keeps focus on parent structural ops (sorted/collapse/duplicate)
                    // without requiring the full Sortable + set child tree.
                    ({ wrapper } = await mountReplicator({ profile, shallow: true }));
                    vm = wrapper.vm;
                    perf.reset();
                })();
            }

            await ready;

            return { wrapper, vm };
        },
        cleanup() {
            wrapper?.unmount();
            wrapper = null;
            vm = null;
            ready = null;
        },
    };
}

describe('replicator structural ops', () => {
    const ctx = createReplicatorContext('medium');

    bench(
        'collapseAll',
        async () => {
            const { vm } = await ctx.ensure();
            perf.start('bench.replicator.collapseAll');
            vm.collapseAll();
            await nextTick();
            perf.stop('bench.replicator.collapseAll');
            vm.expandAll();
            await nextTick();
        },
        { iterations: 20, warmupIterations: 3 },
    );

    bench(
        'expandAll',
        async () => {
            const { vm } = await ctx.ensure();
            vm.collapseAll();
            await nextTick();
            perf.start('bench.replicator.expandAll');
            vm.expandAll();
            await nextTick();
            perf.stop('bench.replicator.expandAll');
        },
        { iterations: 20, warmupIterations: 3 },
    );

    bench(
        'sorted (reorder)',
        async () => {
            const { vm } = await ctx.ensure();
            const value = [...vm.value];
            if (value.length < 2) return;

            const reordered = [value[1], value[0], ...value.slice(2)];

            perf.start('bench.replicator.sorted');
            vm.sorted(reordered);
            await nextTick();
            perf.stop('bench.replicator.sorted');

            vm.sorted(value);
            await nextTick();
        },
        { iterations: 20, warmupIterations: 3 },
    );

    bench(
        'duplicateSet',
        async () => {
            const { vm } = await ctx.ensure();
            const originalLength = vm.value.length;
            const id = vm.value[0]._id;

            perf.start('bench.replicator.duplicateSet');
            vm.duplicateSet(id);
            await nextTick();
            perf.stop('bench.replicator.duplicateSet');

            if (vm.value.length > originalLength) {
                vm.removed(vm.value[1], 1);
                await nextTick();
            }
        },
        { iterations: 10, warmupIterations: 2 },
    );
});
