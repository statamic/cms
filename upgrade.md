
# Vue 3 upgrade guide
This document describes the things still to do, in progress and what should be done by addon authors.

## Model values
The way v-model works has changed. For more info see: https://v3-migration.vuejs.org/breaking-changes/v-model.html
- `:value` should become `:model-value`
- `@input="someHandler"` should become `@update:model-value="someHandler"`. 
Existing `v-model` bindings are not affected.

## v-calendar: v2 -> v3 (todo)
If you're using v-calendar in any of your addons, please follow the [upgrade guide](https://vcalendar.io/getting-started/upgrade-guide.html).

## vue-select v3 -> v4.beta (in progress)
See https://github.com/sagalbot/vue-select/issues/1597

## vue-clickaway: replaced (done)
Any instances of `v-on-clickaway` should be replaced with `v-click-away`.

## vue-js-modal 2.0.1 (todo) 
Not available for vue 3. What to do?
https://euvl.github.io/vue-js-modal/Properties.html#properties-2

Use another nice accessible modal?

## vue-toasted replaced with vue-toastification (in progress)
Vue-toasted is only available for vue 2. We still need to style the new toasts.
Also document the new api changes between the two packages.
For example, the `duration` option is now called `timeout`.