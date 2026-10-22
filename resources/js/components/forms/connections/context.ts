import type { ComputedRef, InjectionKey } from 'vue';

export type ConnectionContext = {
    connection: Record<string, unknown>;
    errors: Record<string, string[]>;
    meta: Record<string, unknown>;
    blueprint?: Record<string, any>;
    name: string;
};

export const connectionContextKey: InjectionKey<ComputedRef<ConnectionContext>> = Symbol('connection');
