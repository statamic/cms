import { bench, describe } from 'vitest';
import {
    mountBard,
    mountReplicator,
    mountPublishWithBard,
} from '../helpers/mount.js';

describe('mount time', () => {
    bench(
        'bard small',
        async () => {
            const { wrapper } = await mountBard({ profile: 'small', withSets: false });
            wrapper.unmount();
        },
        { iterations: 5, warmupIterations: 1 },
    );

    bench(
        'bard medium',
        async () => {
            const { wrapper } = await mountBard({ profile: 'medium', withSets: false });
            wrapper.unmount();
        },
        { iterations: 3, warmupIterations: 1 },
    );

    bench(
        'replicator small',
        async () => {
            const { wrapper } = await mountReplicator({ profile: 'small', shallow: true });
            wrapper.unmount();
        },
        { iterations: 5, warmupIterations: 1 },
    );

    bench(
        'replicator medium',
        async () => {
            const { wrapper } = await mountReplicator({ profile: 'medium', shallow: true });
            wrapper.unmount();
        },
        { iterations: 3, warmupIterations: 1 },
    );

    bench(
        'publish form + bard medium',
        async () => {
            const wrapper = await mountPublishWithBard('medium');
            wrapper.unmount();
        },
        { iterations: 3, warmupIterations: 1 },
    );

    // Full publish-form + replicator mounts are covered by fixture/serialization benches
    // plus shallow replicator mounts above until set child wiring is hardened for browser mode.
});
