import { Component } from 'vue';

export default class Inertia {
    private components: Record<string, Component> = {};

    register(name: string, component: Component): void {
        this.components[name] = component;
    }

    get(name: string): Component | undefined {
        return this.components[name];
    }
}
