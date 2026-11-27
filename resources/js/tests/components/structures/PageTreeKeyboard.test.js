import { expect, test, vi } from 'vitest';
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
        $refs: { tree: { stats: top, move: vi.fn() } },
        collapsedState: ['about', 'team'],
        treeUpdated: vi.fn(),
    };
    for (const method of ['isRoot', 'siblingsOf', 'heightOf', 'canKeyboardMove', 'keyboardMove']) {
        self[method] = PageTree.methods[method].bind(self);
    }

    return { can: self.canKeyboardMove, move: self.keyboardMove, self, home, about, team, jobs, contact };
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

test('moving up or down moves it to the next place at its level, and marks the tree as changed', () => {
    const { move, self, about, contact } = tree();

    move(contact, 'up');
    expect(self.$refs.tree.move).toHaveBeenCalledWith(contact, null, 1);

    move(about, 'down');
    expect(self.$refs.tree.move).toHaveBeenLastCalledWith(about, null, 2);
    expect(self.treeUpdated).toHaveBeenCalledTimes(2);
});

test('nesting puts it last under the page above, and opens that page', () => {
    const { move, self, about, contact } = tree();

    move(contact, 'in');

    expect(self.$refs.tree.move).toHaveBeenCalledWith(contact, about, 1);
    expect(about.open).toBe(true);
    expect(self.collapsedState).toEqual(['team']);
});

test('moving out a level puts it just after its old parent', () => {
    const { move, self, about, team, jobs } = tree();

    move(jobs, 'out');
    expect(self.$refs.tree.move).toHaveBeenCalledWith(jobs, about, 1);

    move(team, 'out');
    expect(self.$refs.tree.move).toHaveBeenLastCalledWith(team, null, 2);
});

test('a move it cannot make does nothing', () => {
    const { move, self, home, about } = tree();

    move(home, 'down');
    move(about, 'up');

    expect(self.$refs.tree.move).not.toHaveBeenCalled();
    expect(self.treeUpdated).not.toHaveBeenCalled();
});

test('a page is only a Tab stop while it has focus, so Tab goes from handle to handle', () => {
    const el = document.createElement('div');
    el.innerHTML = '<div class="tree-node" tabindex="0"></div><button>handle</button>';
    document.body.append(el);
    const [page, handle] = el.children;
    const stat = { data: { id: 'about' } };
    const self = { $refs: { tree: { $el: el, activeDescendant: stat } } };
    const drop = PageTree.methods.dropTreeTabStop.bind(self);

    page.focus();
    drop();
    expect(self.$refs.tree.activeDescendant).toBe(stat);

    handle.focus();
    drop();
    expect(self.$refs.tree.activeDescendant).toBe(null);

    el.remove();
});
