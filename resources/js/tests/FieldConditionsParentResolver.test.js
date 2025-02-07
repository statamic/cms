import { test, expect } from 'vitest';
import ParentResolver from '../components/field-conditions/ParentResolver.js';

let resolve = function (currentFieldPath, pathWithParent) {
    return new ParentResolver(currentFieldPath).resolve(pathWithParent);
};

test('it resolves from group to top level', () => {
    expect(resolve('group.field', '$parent.name')).toEqual('$root.name');
});

test('it resolves from set/row to top level', () => {
    expect(resolve('replicator.0.field', '$parent.name')).toEqual('$root.name');
    expect(resolve('grid.0.field', '$parent.name')).toEqual('$root.name');
    expect(resolve('bard.0.field', '$parent.name')).toEqual('$root.name');
});

test('it resolves from nested group to parent group', () => {
    expect(resolve('group.nested_group.field', '$parent.name')).toEqual('$root.group.name');
});

test('it resolves from nested set to parent set', () => {
    expect(resolve('replicator.2.nested_replicator.1.field', '$parent.name')).toEqual('$root.replicator.2.name');
    expect(resolve('grid.2.nested_grid.1.field', '$parent.name')).toEqual('$root.grid.2.name');
    expect(resolve('bard.2.nested_bard.1.field', '$parent.name')).toEqual('$root.bard.2.name');
});

test('it resolves from nested group to parent set', () => {
    expect(resolve('replicator.1.group.field', '$parent.name')).toEqual('$root.replicator.1.name');
    expect(resolve('grid.1.group.field', '$parent.name')).toEqual('$root.grid.1.name');
    expect(resolve('bard.1.group.field', '$parent.name')).toEqual('$root.bard.1.name');
});

test('it resolves from nested set to parent group', () => {
    expect(resolve('group.replicator.1.field', '$parent.name')).toEqual('$root.group.name');
    expect(resolve('group.grid.1.field', '$parent.name')).toEqual('$root.group.name');
    expect(resolve('group.bard.1.field', '$parent.name')).toEqual('$root.group.name');
});

test('it resolves from deeply nested groups all the way up to top level', () => {
    let fromField = 'group.nested_group.deeper_group.deeeeeeeper_group.field';

    expect(resolve(fromField, '$parent.name')).toEqual('$root.group.nested_group.deeper_group.name');
    expect(resolve(fromField, '$parent.$parent.name')).toEqual('$root.group.nested_group.name');
    expect(resolve(fromField, '$parent.$parent.$parent.name')).toEqual('$root.group.name');
    expect(resolve(fromField, '$parent.$parent.$parent.$parent.name')).toEqual('$root.name');
});

test('it resolves from deeply nested sets all the way up to top level', () => {
    let fromField = 'replicator.1.bard.4.grid.0.replicator.6.field';

    expect(resolve(fromField, '$parent.name')).toEqual('$root.replicator.1.bard.4.grid.0.name');
    expect(resolve(fromField, '$parent.$parent.name')).toEqual('$root.replicator.1.bard.4.name');
    expect(resolve(fromField, '$parent.$parent.$parent.name')).toEqual('$root.replicator.1.name');
    expect(resolve(fromField, '$parent.$parent.$parent.$parent.name')).toEqual('$root.name');
});

test('it resolves from deeply nested mix of everything all the way up to top level', () => {
    let fromField = 'group.replicator.1.group.bard.4.grid.0.group.group.replicator.6.field';

    expect(resolve(fromField, '$parent.name')).toEqual('$root.group.replicator.1.group.bard.4.grid.0.group.group.name');
    expect(resolve(fromField, '$parent.$parent.name')).toEqual(
        '$root.group.replicator.1.group.bard.4.grid.0.group.name',
    );
    expect(resolve(fromField, '$parent.$parent.$parent.name')).toEqual(
        '$root.group.replicator.1.group.bard.4.grid.0.name',
    );
    expect(resolve(fromField, '$parent.$parent.$parent.$parent.name')).toEqual(
        '$root.group.replicator.1.group.bard.4.name',
    );
    expect(resolve(fromField, '$parent.$parent.$parent.$parent.$parent.name')).toEqual(
        '$root.group.replicator.1.group.name',
    );
    expect(resolve(fromField, '$parent.$parent.$parent.$parent.$parent.$parent.name')).toEqual(
        '$root.group.replicator.1.name',
    );
    expect(resolve(fromField, '$parent.$parent.$parent.$parent.$parent.$parent.$parent.name')).toEqual(
        '$root.group.name',
    );
    expect(resolve(fromField, '$parent.$parent.$parent.$parent.$parent.$parent.$parent.$parent.name')).toEqual(
        '$root.name',
    );
});
