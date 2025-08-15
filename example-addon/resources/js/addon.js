import MyCustomComponent from './components/MyCustomComponent.vue';
Statamic.booting(() => {
    Statamic.$components.register('my-custom-component', MyCustomComponent);
});
