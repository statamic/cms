I'm currently working on our Storybook documentation for our UI Components.

Up until now, we've stored the descriptions for props and events inside the `argTypes` object in component stories.

This
_works_ most of the time, but not for subcomponents. To keep everything consistent, I want to move the prop descriptions into JSDoc comments inside the components themselves.

So, this:

```js
limit: {
    control: 'number',
        description
:
    'Specify a character limit',
}
,
```

Would turn into this:

```js
defineProps({
    /** Specify a character limit */
    limit: {type: Number},
});
```

You can then remove the prop/arg from the
`argTypes` array as it'll be picked up automatically from Storybook. However, DO NOT remove them in the following cases:

* `control: 'select'` - the `description` should be removed, but the control and options should remain
* If it's an event (you can check the component's
  `defineEmits()` to confirm if smth is an event), then keep it in the array WITH its description.

* If you're unsure of anything, let me know.