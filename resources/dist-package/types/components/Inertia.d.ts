import { Component } from 'vue';
export default class Inertia {
    private components;
    register(name: string, component: Component): void;
    get(name: string): Component | undefined;
}
