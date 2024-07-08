
# Vue 3 upgrade guide
This document describes the things still to do, in progress and what should be done by addon authors.

## v-calendar: v2 -> v3 (todo)
If you're using v-calendar in any of your addons, please follow the [upgrade guide](https://vcalendar.io/getting-started/upgrade-guide.html).

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