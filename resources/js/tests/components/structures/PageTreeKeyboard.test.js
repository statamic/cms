import { expect, test } from 'vitest';
import PageTree from '@/components/structures/PageTree.vue';

// The rules for moving a page with the keyboard (see Branch.vue), run against a made-up tree:
//
//   Home (root, when the structure expects one)
//   About
//     Team
//       Jobs
//   Contact
function tree({ expectsRoot = true, maxDepth = Infinity } = {}) {
    const node = (id, children = []) => ({ data: { id }, children, parent: null, level: 1 });
    const jobs = node('jobs');
    const team = node('team', [jobs]);
    const about = node('about', [team]);
    const home = node('home');
    const contact = node('contact');
    const top = [home, about, contact];

    const link = (stat, parent, level) => {
        stat.parent = parent;
        stat.level = level;
        stat.children.forEach((child) => link(child, stat, level + 1));
    };
    top.forEach((stat) => link(stat, null, 1));

    const self = {
        expectsRoot,
        maxDepth,
        treeData: top.map((stat) => stat.data),
        $refs: { tree: { stats: top } },
    };
    for (const method of ['isRoot', 'siblingsOf', 'heightOf', 'canKeyboardMove']) {
        self[method] = PageTree.methods[method].bind(self);
    }

    return { can: self.canKeyboardMove, home, about, team, jobs, contact };
}

test('the root page never moves, and nothing moves above it', () => {
    const { can, home, about } = tree();

    expect(['up', 'down', 'in', 'out'].some((direction) => can(home, direction))).toBe(false);
    expect(can(about, 'up')).toBe(false);
    expect(can(about, 'in')).toBe(false);
});

test('without a root, the first page can move down and the second up', () => {
    const { can, home, about } = tree({ expectsRoot: false });

    expect(can(home, 'down')).toBe(true);
    expect(can(about, 'up')).toBe(true);
});

test('a page moves up and down within its level', () => {
    const { can, about, contact, team } = tree();

    expect(can(about, 'down')).toBe(true);
    expect(can(contact, 'up')).toBe(true);
    expect(can(contact, 'down')).toBe(false);
    expect(can(team, 'up')).toBe(false);
    expect(can(team, 'down')).toBe(false);
});

test('a page nests under the page above, and a child moves out a level', () => {
    const { can, contact, team, about } = tree();

    expect(can(contact, 'in')).toBe(true);
    expect(can(team, 'out')).toBe(true);
    expect(can(about, 'out')).toBe(false);
});

test('nothing nests deeper than the max depth, counting the children it brings', () => {
    // Contact under About would be level 2
    const two = tree({ maxDepth: 2 });
    expect(two.can(two.contact, 'in')).toBe(true);

    const one = tree({ maxDepth: 1 });
    expect(one.can(one.contact, 'in')).toBe(false);
});

test('a page brings its children with it, so they count towards the max depth too', () => {
    // About under Home would put About at level 2, Team at 3 and Jobs at 4
    const deep = tree({ expectsRoot: false, maxDepth: 4 });
    expect(deep.can(deep.about, 'in')).toBe(true);

    const shallow = tree({ expectsRoot: false, maxDepth: 3 });
    expect(shallow.can(shallow.about, 'in')).toBe(false);
});
