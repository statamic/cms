import { bench, describe } from 'vitest';
import {
    makeBardValueFromProfile,
    makeReplicatorValueFromProfile,
} from '../fixtures/index.js';

function countNodes(nodes) {
    if (!nodes || !Array.isArray(nodes)) return 0;
    let count = nodes.length;
    nodes.forEach((node) => {
        if (node.content) {
            count += countNodes(node.content);
        }
        if (node.attrs?.values?.body) {
            count += countNodes(node.attrs.values.body);
        }
    });
    return count;
}

describe('fixture generation', () => {
    bench('bard small', () => {
        makeBardValueFromProfile('small');
    });

    bench('bard medium', () => {
        makeBardValueFromProfile('medium');
    });

    bench('bard pathological', () => {
        makeBardValueFromProfile('pathological');
    });

    bench('replicator small', () => {
        makeReplicatorValueFromProfile('small');
    });

    bench('replicator medium', () => {
        makeReplicatorValueFromProfile('medium');
    });

    bench('replicator pathological', () => {
        makeReplicatorValueFromProfile('pathological');
    });
});

describe('serialization hot paths (bard medium)', () => {
    const value = makeBardValueFromProfile('medium');

    bench('JSON.stringify bard value', () => {
        JSON.stringify(value);
    });

    bench('clone via JSON.parse(JSON.stringify)', () => {
        JSON.parse(JSON.stringify(value));
    });

    bench('countNodes', () => {
        countNodes(value);
    });
});

describe('serialization hot paths (bard pathological)', () => {
    const value = makeBardValueFromProfile('pathological');

    bench('JSON.stringify bard value', () => {
        JSON.stringify(value);
    });

    bench('clone via JSON.parse(JSON.stringify)', () => {
        JSON.parse(JSON.stringify(value));
    });

    bench('countNodes', () => {
        countNodes(value);
    });
});
