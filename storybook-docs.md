I'm currently in the process of adding our remaining UI Components to our Storybook documentation site, so third-party developers can easily use our components.

When I ask you to add documentation for a page, please keep these things in mind:

* I want you to add docs for the component, not "canvas" stuff. That means you need to create an `.mdx` file.
* Code snippets should *mostly* live in variables, to avoid duplicating it between the code snippet and the example.
    * If you want to display something in the example that's not in the code snippet, then use backticks to add in the snippet with whatever you need to add.
* Some components live by themselves, some have "child" / "related" components. In this case, the child/related components should be listed as
  `subcomponents`.
* I want you to document the props and events for each component.
* Please document them inside the `argTypes` object, rather than JSDoc in the component's code.
    * Although, don't do this for subcomponents as they can't be documented the same way.
    * When the prop has multiple options, it should look like the below code snippet. A
      `select` control, with a description describing what it does, and `<br><br> Options: ...`, as well as an
      `options` array

```ts
      size: {
    control: 'select',
        description
:
    'Controls the size of the toggle items. <br><br> Options: `xs`, `sm`, `base`',
        options
:
    ['xs', 'sm', 'base'],
}
,
```

    * When dealing with `size` or `variant` props, please use this description format: `Controls the size of the ...` / `Controls the appearance of the ...`
    * When dealing with `disabled`, `required` or `read-only` props, don't add a description or anything. It's fairly self-explanatory.
    * When dealing with a `boolean` prop, please use this description format: "When `true`, ..."
    * When dealing with an `icon` prop (or similar), the argument should look like the below code snippet. Make sure to import the `icons` file from `./icons`:

```ts
      icon: {
    control: 'select',
        options
:
    icons,
        description
:
    'Icon name. [Browse available icons](/?path=/story/components-icon--all-icons)',
}
,
```

    * When the component has events (like `update:modelValue`), you should add them to the `argTypes` object like the below code snippet. The description should be something along the lines of `Event handler called when the ... is changed`

```ts
      'update:modelValue'
:
{
    description: 'Event handler called when the input is updated.',
        table
:
    {
        category: 'events',
            type
    :
        {
            summary: '(value: string) => void'
        }
    }
}
```

If you have any doubts about convention, please look at other documented components or ask me.