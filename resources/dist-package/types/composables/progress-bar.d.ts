export default function useProgressBar(): {
    loading: typeof loading;
    start: typeof add;
    complete: typeof remove;
    names: typeof names;
    count: typeof count;
    isComplete: typeof isComplete;
};
declare function loading(name: any, loading: any): void;
declare function add(name: any): void;
declare function remove(name: any): void;
declare function names(): never[];
declare function count(): number;
declare function isComplete(): boolean;
export {};
