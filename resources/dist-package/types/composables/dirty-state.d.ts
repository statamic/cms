export default function useDirtyState(): {
    state: typeof state;
    add: typeof add;
    remove: typeof remove;
    names: typeof names;
    count: typeof count;
    has: typeof has;
    disableWarning: typeof disableWarning;
};
declare function state(name: any, state: any): void;
declare function add(name: any): void;
declare function remove(name: any): void;
declare function names(): never[];
declare function count(): number;
declare function has(name: any): boolean;
declare function disableWarning(): void;
export {};
