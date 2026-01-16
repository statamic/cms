<script setup>
import Head from '@/pages/layout/Head.vue';

defineProps(['icons']);
</script>

<template>
    <Head :title="__('Playground')" />

    <ui-header title="Playground" icon="playground" class="starting-style-transition" v-cloak>
        <ui-subheading>A collection of components to test and play with.</ui-subheading>
    </ui-header>

    <ui-dropdown>
    <template #trigger>
        <ui-button text="My Account" icon-append="ui/chevron-vertical" class="[&_svg]:size-2" />
    </template>
    <ui-dropdown-header text="My Account" icon="avatar" />
    <ui-dropdown-menu>
        <ui-dropdown-item text="Photos" icon="assets" />
        <ui-dropdown-item text="Email" icon="mail" />
        <ui-dropdown-item text="Sales" icon="taxonomies" />
    </ui-dropdown-menu>
    <ui-dropdown-footer text="Logout" icon="arrow-right" />
</ui-dropdown>

    <div v-cloak class="space-y-12">
        <div class="space-y-12 prose starting-style-transition starting-style-transition-children">
            <h1>Typography <strong>Test</strong> Document</h1>
            <p class="lead">This comprehensive document tests all typography elements supported by both tw-prose and @tailwindcss/typography plugins.</p>
            <h2>Headings</h2>
            <h1>Heading Level 1</h1>
            <h2>Heading Level 2</h2>
            <h3>Heading Level 3</h3>
            <h4>Heading Level 4</h4>
            <h5>Heading Level 5</h5>
            <h6>Heading Level 6</h6>
            <h2>Paragraphs and Text Formatting</h2>
            <p>This is a regular paragraph with <strong>bold text</strong>, <em>italic text</em>, <u>underlined text</u>, and <mark>highlighted text</mark>. You can also have <small>small text</small> and text with <sup>superscript</sup> and <sub>subscript</sub>.</p>
            <p>Here's a paragraph with a <a href="#test">link to test</a> the link styling. Links should have proper hover states and visual distinction from regular text.</p>
            <h2>Lists</h2>
            <h3>Unordered List</h3>
            <ul>
                <li>First item in unordered list</li>
                <li>Second item with <strong>bold text</strong></li>
                <li>
                    Third item with nested list:
                    <ul>
                        <li>Nested item 1</li>
                        <li>Nested item 2</li>
                    </ul>
                </li>
                <li>Fourth item</li>
            </ul>
            <h3>Ordered List</h3>
            <ol>
                <li>First numbered item</li>
                <li>Second numbered item</li>
                <li>
                    Third item with nested ordered list:
                    <ol>
                        <li>Nested numbered item 1</li>
                        <li>Nested numbered item 2</li>
                    </ol>
                </li>
                <li>Fourth numbered item</li>
            </ol>
            <h3>Definition List</h3>
            <dl>
                <dt>Term 1</dt>
                <dd>Definition for term 1 with detailed explanation.</dd>
                <dt>Term 2</dt>
                <dd>Definition for term 2.</dd>
                <dt>Term 3</dt>
                <dd>Another definition with <em>emphasized text</em>.</dd>
            </dl>
            <h2>Blockquotes</h2>
            <blockquote>
                <p>This is a blockquote with a single paragraph. Blockquotes are used to highlight quoted text or important information.</p>
            </blockquote>
            <blockquote>
                <p>This is a blockquote with multiple paragraphs. The first paragraph introduces the quote.</p>
                <p>The second paragraph continues the quoted content and shows how multiple paragraphs are handled within blockquotes.</p>
                <cite> -  Author Name, Source Title</cite>
            </blockquote>
            <h2>Code Examples</h2>
            <p>Inline code can be written using <code>console.log('Hello, World!')</code> for JavaScript or <code>printf("Hello, World!")</code> for C.</p>
            <h3>Code Blocks</h3>
            <pre>
                <code>function fibonacci(n) {
                if (n &lt;= 1) return n;
                return fibonacci(n - 1) + fibonacci(n - 2);
                }

                // Usage example
                console.log(fibonacci(10)); // Output: 55</code>
            </pre>
            <h3>Keyboard Input</h3>
            <p>To save a file, press <kbd>Ctrl</kbd> + <kbd>S</kbd> on Windows or <kbd>Cmd</kbd> + <kbd>S</kbd> on Mac.</p>
            <h2>Tables</h2>
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>City</th>
                    <th>Occupation</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>John Doe</td>
                    <td>28</td>
                    <td>New York</td>
                    <td>Software Developer</td>
                </tr>
                <tr>
                    <td>Jane Smith</td>
                    <td>32</td>
                    <td>San Francisco</td>
                    <td>UX Designer</td>
                </tr>
                <tr>
                    <td>Bob Johnson</td>
                    <td>45</td>
                    <td>Chicago</td>
                    <td>Project Manager</td>
                </tr>
                </tbody>
            </table>
            <h2>Images and Media</h2>
            <figure>
                <img src="https://picsum.photos/600/300" alt="Sample image for testing">
                <figcaption>Figure 1: A sample image with caption to test image styling and figure elements.</figcaption>
            </figure>
            <h2>Horizontal Rules</h2>
            <p>Content above the horizontal rule.</p>
            <hr>
            <p>Content below the horizontal rule.</p>
            <h2>Additional Text Elements</h2>
            <p>This paragraph tests various inline elements: <abbr title="HyperText Markup Language">HTML</abbr> abbreviation, <span class="time-element">January 1, 2024</span> time element, and <span>generic span element</span>.</p>
            <address> Contact Information:<br> John Developer<br> 123 Code Street<br> Tech City, TC 12345<br> Email: john@example.com </address>
            <h2>Complex Nested Content</h2>
            <div>
                <h3>Nested Structure Test</h3>
                <p>This section tests how nested content is handled:</p>
                <ol>
                    <li>First item with <strong>bold</strong> and <em>italic</em> text</li>
                    <li>
                        Second item containing a blockquote:
                        <blockquote>
                            <p>Nested blockquote within a list item.</p>
                        </blockquote>
                    </li>
                    <li>Third item with code: <code>nested.code.example()</code></li>
                </ol>
                <p>Final paragraph to close the nested structure test.</p>
            </div>
            <h2>Long Form Content</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>
        </div>
        <div class="space-y-12 prose">
            <section class="space-y-4">
                <p>This is some <code>code</code> in a paragraph.</p>
                <pre>
                    <code>$this is some code in a pre tag.
                    with another line.
                    </code>
                </pre>
            </section>
            <section class="space-y-4 prose">
                <p>This is some <code>code</code> in a paragraph.</p>
                <pre>
                    <code>$this is some code in a pre tag.
                    with another line.
                    </code>
                </pre>
            </section>
        </div>

        <div class="space-y-12">
            <section class="space-y-4">
                <ui-heading size="lg">Badges</ui-heading>
                <div class="mb-4 flex gap-2 sm:gap-3 items-end">
                    <ui-badge size="lg" text="Green" color="green" />
                    <ui-badge size="lg" text="Red" color="red" />
                    <ui-badge size="lg" text="Black" color="black" />
                    <ui-badge text="Blue" color="blue" />
                    <ui-badge text="Amber" color="amber" />
                    <ui-badge text="Pink" color="pink" />
                    <ui-badge size="sm" text="Cyan" color="cyan" />
                    <ui-badge size="sm" text="Purple" color="purple" />
                    <ui-badge size="sm" text="Gray" color="gray" />
                    <ui-badge size="sm" text="Default" />
                    <ui-badge size="sm" text="Rose" color="rose" />
                    <ui-badge size="sm" text="Emerald" color="emerald" />
                    <ui-badge size="sm" text="Fuchsia" color="fuchsia" />
                    <ui-badge size="sm" text="Indigo" color="indigo" />
                    <ui-badge size="sm" text="Lime" color="lime" />
                    <ui-badge size="sm" text="Orange" color="orange" />
                    <ui-badge size="sm" text="Sky" color="sky" />
                    <ui-badge size="sm" text="Teal" color="teal" />
                    <ui-badge size="sm" text="Violet" color="violet" />
                    <ui-badge size="sm" text="White" color="white" />
                    <ui-badge size="sm" text="Yellow" color="yellow" />
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Badges as Links</ui-heading>
                <div class="mb-4 flex gap-2 sm:gap-3 items-end flex-wrap">
                    <ui-badge size="lg" text="Green Link" color="green" href="#" />
                    <ui-badge size="lg" text="Red Link" color="red" href="#" />
                    <ui-badge size="lg" text="Black Link" color="black" href="#" />
                    <ui-badge text="Blue Link" color="blue" href="#" />
                    <ui-badge text="Amber Link" color="amber" href="#" />
                    <ui-badge text="Pink Link" color="pink" href="#" />
                    <ui-badge size="sm" text="Cyan Link" color="cyan" href="#" />
                    <ui-badge size="sm" text="Purple Link" color="purple" href="#" />
                    <ui-badge size="sm" text="Gray Link" color="gray" href="#" />
                    <ui-badge size="sm" text="Default Link" href="#" />
                    <ui-badge size="sm" text="Rose Link" color="rose" href="#" />
                    <ui-badge size="sm" text="Emerald Link" color="emerald" href="#" />
                    <ui-badge size="sm" text="Fuchsia Link" color="fuchsia" href="#" />
                    <ui-badge size="sm" text="Indigo Link" color="indigo" href="#" />
                    <ui-badge size="sm" text="Lime Link" color="lime" href="#" />
                    <ui-badge size="sm" text="Orange Link" color="orange" href="#" />
                    <ui-badge size="sm" text="Sky Link" color="sky" href="#" />
                    <ui-badge size="sm" text="Teal Link" color="teal" href="#" />
                    <ui-badge size="sm" text="Violet Link" color="violet" href="#" />
                    <ui-badge size="sm" text="White Link" color="white" href="#" />
                    <ui-badge size="sm" text="Yellow Link" color="yellow" href="#" />
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Badges as Buttons</ui-heading>
                <div class="mb-4 flex gap-2 sm:gap-3 items-end flex-wrap">
                    <ui-badge size="lg" text="Green Button" color="green" as="button" />
                    <ui-badge size="lg" text="Red Button" color="red" as="button" />
                    <ui-badge size="lg" text="Black Button" color="black" as="button" />
                    <ui-badge text="Blue Button" color="blue" as="button" />
                    <ui-badge text="Amber Button" color="amber" as="button" />
                    <ui-badge text="Pink Button" color="pink" as="button" />
                    <ui-badge size="sm" text="Cyan Button" color="cyan" as="button" />
                    <ui-badge size="sm" text="Purple Button" color="purple" as="button" />
                    <ui-badge size="sm" text="Gray Button" color="gray" as="button" />
                    <ui-badge size="sm" text="Default Button" as="button" />
                    <ui-badge size="sm" text="Rose Button" color="rose" as="button" />
                    <ui-badge size="sm" text="Emerald Button" color="emerald" as="button" />
                    <ui-badge size="sm" text="Fuchsia Button" color="fuchsia" as="button" />
                    <ui-badge size="sm" text="Indigo Button" color="indigo" as="button" />
                    <ui-badge size="sm" text="Lime Button" color="lime" as="button" />
                    <ui-badge size="sm" text="Orange Button" color="orange" as="button" />
                    <ui-badge size="sm" text="Sky Button" color="sky" as="button" />
                    <ui-badge size="sm" text="Teal Button" color="teal" as="button" />
                    <ui-badge size="sm" text="Violet Button" color="violet" as="button" />
                    <ui-badge size="sm" text="White Button" color="white" as="button" />
                    <ui-badge size="sm" text="Yellow Button" color="yellow" as="button" />
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Buttons</ui-heading>
                <div class="mb-4 flex gap-2 sm:gap-3 items-end">
                    <ui-button text="Default" />
                    <ui-button text="Primary" variant="primary" />
                    <ui-button text="Filled" variant="filled" />
                    <ui-button text="Ghost" variant="ghost" />
                    <ui-button text="Subtle" variant="subtle" />
                </div>
                <div class="mb-4 flex gap-2 sm:gap-3 items-end">
                    <ui-button icon="save" text="Default" />
                    <ui-button icon="save" text="Primary" variant="primary" />
                    <ui-button icon="save" text="Danger" variant="danger" />
                    <ui-button text="Danger Disabled" variant="danger" disabled />
                    <ui-button icon="save" text="Filled" variant="filled" />
                    <ui-button icon="save" text="Ghost" variant="ghost" />
                    <ui-button icon="save" text="Subtle" variant="subtle" />
                </div>
                <div class="mb-4 flex gap-2 sm:gap-3 items-end">
                    <ui-button icon="save" />
                    <ui-button icon="save" variant="primary" />
                    <ui-button icon="save" variant="filled" />
                    <ui-button icon="save" variant="ghost" />
                    <ui-button icon="save" variant="subtle" />
                </div>
                <div class="mb-4 flex gap-2 sm:gap-3 items-end">
                    <ui-button-group>
                        <ui-button variant="default">Oldest</ui-button>
                        <ui-button variant="default">Newest</ui-button>
                        <ui-button variant="default">Top</ui-button>
                        <ui-button variant="danger">Danger</ui-button>
                    </ui-button-group>
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Calendar</ui-heading>
                <div class="flex gap-6">
                    <ui-card>
                        <ui-calendar />
                    </ui-card>
                    <ui-card>
                        <ui-calendar :number-of-months="2" />
                    </ui-card>
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Card</ui-heading>
                <div class="flex items-center justify-center bg-gray-50 rounded-xl border border-gray-200 p-12">
                    <ui-card class="w-80 space-y-6">
                        <header>
                            <ui-heading size="lg">Create a new account</ui-heading>
                            <ui-subheading>Welcome to the thing! You're gonna love it here.</ui-subheading>
                        </header>
                        <ui-input label="Name" placeholder="Your name" />
                        <ui-input label="Email" type="email" placeholder="Your email" />
                        <div class="space-y-2 pt-6">
                            <ui-button variant="primary" class="w-full" text="Continue" type="submit" />
                            <ui-button variant="ghost" class="w-full">Already have an account? Go sign in</ui-button>
                        </div>
                    </ui-card>
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Card Panel</ui-heading>
                <ui-card-panel heading="Card Panel">This is a card panel.</ui-card-panel>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Checkboxes</ui-heading>
                <div class="flex">
                    <ui-checkbox-group name="meals" label="Select your favorite meals">
                        <ui-checkbox
                            label="Breakfast"
                            description="The morning meal. Should include eggs."
                            value="breakfast"
                        />
                        <ui-checkbox label="Lunch" description="The mid-day meal. Should be protein heavy." value="lunch" />
                        <ui-checkbox label="Dinner" description="The evening meal. Should be delicious." value="dinner" />
                    </ui-checkbox-group>
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Datepicker</ui-heading>
                <div class="flex">
                    <ui-date-picker />
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Dropdown</ui-heading>
                <div class="flex">
                    <ui-dropdown>
                        <template #trigger>
                            <ui-button text="Do a Action" variant="filled" icon-append="chevron-vertical" class="[&_svg]:size-2" />
                        </template>
                        <ui-dropdown-menu>
                            <ui-dropdown-item text="Bake a food" />
                            <ui-dropdown-item text="Write that book" />
                            <ui-dropdown-item text="Eat this meal" />
                            <ui-dropdown-item text="Lie about larceny" />
                            <ui-dropdown-item text="Save some bird" />
                        </ui-dropdown-menu>
                    </ui-dropdown>
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Icons</ui-heading>
                <div class="grid grid-cols-4 md:grid-cols-6 2xl:grid-cols-10 gap-4">
                    <div v-for="icon in icons" class="bg-gray-50 dark:bg-gray-800 rounded-lg py-6 px-2 flex flex-col items-center gap-2 sm:gap-3">
                        <ui-icon :name="icon" class="size-6" />
                        <span class="text-xs text-gray-500">{{ icon }}</span>
                    </div>
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Input</ui-heading>
                <div class="flex gap-2">
                    <ui-input
                        name="email"
                        type="email"
                        required
                        label="Email"
                        value="Edit me"
                    />
                    <ui-input
                        name="email"
                        type="email"
                        required
                        label="Email"
                        read-only
                        value="Read only. Tab, select, but not edit."
                    />
                    <ui-input
                        name="email"
                        type="email"
                        required
                        label="Email"
                        disabled
                        value="Disabled. Cant touch me."
                    />
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Modal</ui-heading>
                <div class="flex">
                    <ui-modal title="That's Pretty Neat">
                        <template #trigger>
                            <ui-button text="How neat is that?" />
                        </template>
	                    <p>This is some very basic example text to show you what stacks look like with content.</p>
                    </ui-modal>
                </div>
            </section>

	        <section class="space-y-4">
		        <ui-heading size="lg">Stack</ui-heading>
		        <div class="flex">
			        <ui-stack title="That's Pretty Neat">
				        <template #trigger>
					        <ui-button text="How neat is that?" />
				        </template>
				        <p>This is some very basic example text to show you what stacks look like with content.</p>
			        </ui-stack>
		        </div>
	        </section>

            <section class="space-y-4">
                <ui-heading size="lg">Popover</ui-heading>
                <div class="flex">
                    <ui-popover>
                        <template #trigger>
                            <ui-button text="Open Popover" />
                        </template>

                        <div class="flex flex-col gap-2.5">
                            <ui-heading text="Provide Feedback" />
                            <ui-textarea placeholder="How we can make this component better?" elastic />
                            <div class="flex flex-col sm:flex-row sm:justify-end">
                                <ui-button variant="primary" size="sm" text="Submit" />
                            </div>
                        </div>
                    </ui-popover>
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Radio Group</ui-heading>
                <div class="flex">
                    <ui-radio-group name="favorite" label="Choose your favorite meal">
                        <ui-radio
                            label="Breakfast"
                            description="The morning meal. Should include eggs."
                            value="breakfast"
                            checked
                        />
                        <ui-radio label="Lunch" description="The mid-day meal. Should be protein heavy." value="lunch" />
                        <ui-radio label="Dinner" description="The evening meal Should be delicious." value="dinner" />
                    </ui-radio-group>
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Disabled Radio Items</ui-heading>
                <ui-radio-group name="favorite" label="Choose your favorite Star Wars movie">
                    <ui-radio-item label="A New Hope" value="ep4"/>
                    <ui-radio-item label="Empire Strikes Back" value="ep5" />
                    <ui-radio-item label="Return of the Jedi" value="ep6" />
                    <ui-radio-item disabled label="the Force Awakens" value="ep7" />
                    <ui-radio-item disabled label="The Last Jedi" value="ep8" />
                    <ui-radio-item disabled label="The Rise of Skywalker" value="ep9" />
                </ui-radio-group>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Select</ui-heading>
                <div class="flex">
                    <ui-select
                        icon="money-bag-dollar"
                        class="w-full"
                        label="Favorite band"
                        :options="[
                            { label: 'The Midnight', value: 'the_midnight' },
                            { label: 'The 1975', value: 'the_1975' },
                            { label: 'Sunglasses Kid', value: 'sunglasses_kid' }
                        ]"
                    />
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Separator</ui-heading>
                <div class="flex">
                    <ui-separator text="vs" />
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Splitter</ui-heading>
                <div class="flex bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <ui-splitter-group>
                        <ui-splitter-panel class="flex h-24 items-center justify-center rounded-xl bg-white">
                            Left
                        </ui-splitter-panel>
                        <ui-splitter-resize-handle class="w-3" />
                        <ui-splitter-panel class="flex h-24 items-center justify-center rounded-xl bg-white">
                            Right
                        </ui-splitter-panel>
                    </ui-splitter-group>
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Switch</ui-heading>
                <div class="flex items-center gap-2">
                    <ui-switch size="sm" />
                    <ui-switch  />
                    <ui-switch size="lg" />
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Table</ui-heading>
                <div class="flex">
                    <ui-table>
                        <ui-table-columns>
                            <ui-table-column>Product</ui-table-column>
                            <ui-table-column>Stock</ui-table-column>
                            <ui-table-column class="text-right">Price</ui-table-column>
                        </ui-table-columns>
                        <ui-table-rows>
                            <ui-table-row>
                                <ui-table-cell>Mechanical Keyboard</ui-table-cell>
                                <ui-table-cell>
                                    <ui-badge color="green" pill>In Stock</ui-badge>
                                </ui-table-cell>
                                <ui-table-cell class="text-right font-semibold text-gray-925">$159.00</ui-table-cell>
                            </ui-table-row>
                            <ui-table-row>
                                <ui-table-cell>Ergonomic Mouse</ui-table-cell>
                                <ui-table-cell>
                                    <ui-badge color="red" pill>Out of Stock</ui-badge>
                                </ui-table-cell>
                                <ui-table-cell class="text-right font-semibold text-gray-925">$89.00</ui-table-cell>
                            </ui-table-row>
                            <ui-table-row>
                                <ui-table-cell>4K Monitor</ui-table-cell>
                                <ui-table-cell>
                                    <ui-badge color="yellow" pill>Low Stock</ui-badge>
                                </ui-table-cell>
                                <ui-table-cell class="text-right font-semibold text-gray-925">$349.00</ui-table-cell>
                            </ui-table-row>
                        </ui-table-rows>
                    </ui-table>
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Tabs</ui-heading>
                <div class="flex">
                    <ui-tabs default-tab="tab1" class="w-full">
                        <ui-tab-list>
                            <ui-tab-trigger text="Shiny" name="tab1" />
                            <ui-tab-trigger text="Happy" name="tab2" />
                            <ui-tab-trigger text="People" name="tab3" />
                        </ui-tab-list>
                        <ui-tab-content name="tab1">
                            <p class="py-8">Tab 1 content</p>
                        </ui-tab-content>
                        <ui-tab-content name="tab2">
                            <p class="py-8">Tab 2 content</p>
                        </ui-tab-content>
                        <ui-tab-content name="tab3">
                            <p class="py-8">Tab 3 content</p>
                        </ui-tab-content>
                    </ui-tabs>
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Textarea</ui-heading>
                <div class="flex">
                    <ui-textarea label="Message" />
                </div>
            </section>

            <section class="space-y-4">
                <ui-heading size="lg">Tooltip</ui-heading>
                <div class="flex">
                    <ui-button text="Hover me" v-tooltip="'Never gonna give you up'" />
                </div>
            </section>

        </div>
    </div>
</template>
