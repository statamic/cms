import { At as toDisplayString, B as openBlock, C as createVNode, K as resolveComponent, S as createTextVNode, W as renderList, at as withDirectives, f as Fragment, g as createBaseVNode, it as withCtx, q as resolveDirective, x as createStaticVNode, y as createElementBlock } from "./vue.esm-bundler-BbHU-fTn.js";
import { t as _plugin_vue_export_helper_default } from "./_plugin-vue_export-helper-BOaGB7Aw.js";
import { Jn as Heading_default, Oi as Alert_default, in as Description_default } from "./ui-BfR7XN6t.js";
import { r as Head_default } from "./index-Bu3QBQkS.js";
//#region resources/js/pages/Playground.vue
var _sfc_main = {
	__name: "Playground",
	props: ["icons"],
	setup(__props, { expose: __expose }) {
		__expose();
		const __returned__ = {
			Head: Head_default,
			get Alert() {
				return Alert_default;
			},
			get Heading() {
				return Heading_default;
			},
			get Description() {
				return Description_default;
			}
		};
		Object.defineProperty(__returned__, "__isScriptSetup", {
			enumerable: false,
			value: true
		});
		return __returned__;
	}
};
var _hoisted_1 = { class: "space-y-12" };
var _hoisted_2 = { class: "space-y-12" };
var _hoisted_3 = { class: "space-y-4" };
var _hoisted_4 = { class: "mb-4 flex flex-wrap gap-2 sm:gap-3 items-end" };
var _hoisted_5 = { class: "space-y-4" };
var _hoisted_6 = { class: "mb-4 flex flex-wrap gap-2 sm:gap-3 items-end" };
var _hoisted_7 = { class: "space-y-4" };
var _hoisted_8 = { class: "mb-4 flex flex-wrap gap-2 sm:gap-3 items-end" };
var _hoisted_9 = { class: "space-y-4" };
var _hoisted_10 = { class: "mb-4 flex flex-wrap gap-2 sm:gap-3 items-end" };
var _hoisted_11 = { class: "mb-4 flex flex-wrap gap-2 sm:gap-3 items-end" };
var _hoisted_12 = { class: "mb-4 flex flex-wrap gap-2 sm:gap-3 items-end" };
var _hoisted_13 = { class: "mb-4 flex flex-wrap gap-2 sm:gap-3 items-end" };
var _hoisted_14 = { class: "space-y-4" };
var _hoisted_15 = { class: "mb-4 flex flex-wrap gap-2 sm:gap-3 items-end" };
var _hoisted_16 = { class: "mb-4 flex flex-wrap gap-2 sm:gap-3 items-end" };
var _hoisted_17 = { class: "mb-10 space-y-3" };
var _hoisted_18 = { class: "space-y-4" };
var _hoisted_19 = { class: "flex flex-wrap gap-6" };
var _hoisted_20 = { class: "space-y-4" };
var _hoisted_21 = { class: "flex items-center justify-center bg-gray-50 rounded-xl border border-gray-200 p-12" };
var _hoisted_22 = { class: "space-y-2 pt-6" };
var _hoisted_23 = { class: "space-y-4" };
var _hoisted_24 = { class: "space-y-4" };
var _hoisted_25 = { class: "flex" };
var _hoisted_26 = { class: "space-y-4" };
var _hoisted_27 = { class: "flex" };
var _hoisted_28 = { class: "space-y-4" };
var _hoisted_29 = { class: "flex" };
var _hoisted_30 = { class: "space-y-4" };
var _hoisted_31 = { class: "grid grid-cols-4 md:grid-cols-6 2xl:grid-cols-10 gap-4" };
var _hoisted_32 = { class: "bg-gray-50 dark:bg-gray-800 rounded-lg py-6 px-2 flex flex-col items-center gap-2 sm:gap-3" };
var _hoisted_33 = { class: "text-xs text-gray-500" };
var _hoisted_34 = { class: "space-y-4" };
var _hoisted_35 = { class: "flex gap-2" };
var _hoisted_36 = { class: "space-y-4" };
var _hoisted_37 = { class: "flex" };
var _hoisted_38 = { class: "space-y-4" };
var _hoisted_39 = { class: "flex" };
var _hoisted_40 = { class: "space-y-4" };
var _hoisted_41 = { class: "flex" };
var _hoisted_42 = { class: "flex flex-col gap-2.5" };
var _hoisted_43 = { class: "flex flex-col sm:flex-row sm:justify-end" };
var _hoisted_44 = { class: "space-y-4" };
var _hoisted_45 = { class: "flex" };
var _hoisted_46 = { class: "space-y-4" };
var _hoisted_47 = { class: "space-y-4" };
var _hoisted_48 = { class: "flex" };
var _hoisted_49 = { class: "space-y-4" };
var _hoisted_50 = { class: "flex" };
var _hoisted_51 = { class: "space-y-4" };
var _hoisted_52 = { class: "flex bg-gray-50 rounded-xl p-4 border border-gray-200" };
var _hoisted_53 = { class: "space-y-4" };
var _hoisted_54 = { class: "flex items-center gap-2" };
var _hoisted_55 = { class: "space-y-4" };
var _hoisted_56 = { class: "flex" };
var _hoisted_57 = { class: "space-y-4" };
var _hoisted_58 = { class: "flex" };
var _hoisted_59 = { class: "space-y-4" };
var _hoisted_60 = { class: "flex" };
var _hoisted_61 = { class: "space-y-4" };
var _hoisted_62 = { class: "flex" };
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
	const _component_ui_subheading = resolveComponent("ui-subheading");
	const _component_ui_header = resolveComponent("ui-header");
	const _component_ui_button = resolveComponent("ui-button");
	const _component_ui_dropdown_header = resolveComponent("ui-dropdown-header");
	const _component_ui_dropdown_item = resolveComponent("ui-dropdown-item");
	const _component_ui_dropdown_menu = resolveComponent("ui-dropdown-menu");
	const _component_ui_dropdown_footer = resolveComponent("ui-dropdown-footer");
	const _component_ui_dropdown = resolveComponent("ui-dropdown");
	const _component_ui_heading = resolveComponent("ui-heading");
	const _component_ui_badge = resolveComponent("ui-badge");
	const _component_ui_button_group = resolveComponent("ui-button-group");
	const _component_ui_calendar = resolveComponent("ui-calendar");
	const _component_ui_card = resolveComponent("ui-card");
	const _component_ui_input = resolveComponent("ui-input");
	const _component_ui_card_panel = resolveComponent("ui-card-panel");
	const _component_ui_checkbox = resolveComponent("ui-checkbox");
	const _component_ui_checkbox_group = resolveComponent("ui-checkbox-group");
	const _component_ui_date_picker = resolveComponent("ui-date-picker");
	const _component_ui_icon = resolveComponent("ui-icon");
	const _component_ui_modal = resolveComponent("ui-modal");
	const _component_ui_stack = resolveComponent("ui-stack");
	const _component_ui_textarea = resolveComponent("ui-textarea");
	const _component_ui_popover = resolveComponent("ui-popover");
	const _component_ui_radio = resolveComponent("ui-radio");
	const _component_ui_radio_group = resolveComponent("ui-radio-group");
	const _component_ui_radio_item = resolveComponent("ui-radio-item");
	const _component_ui_select = resolveComponent("ui-select");
	const _component_ui_separator = resolveComponent("ui-separator");
	const _component_ui_splitter_panel = resolveComponent("ui-splitter-panel");
	const _component_ui_splitter_resize_handle = resolveComponent("ui-splitter-resize-handle");
	const _component_ui_splitter_group = resolveComponent("ui-splitter-group");
	const _component_ui_switch = resolveComponent("ui-switch");
	const _component_ui_table_column = resolveComponent("ui-table-column");
	const _component_ui_table_columns = resolveComponent("ui-table-columns");
	const _component_ui_table_cell = resolveComponent("ui-table-cell");
	const _component_ui_table_row = resolveComponent("ui-table-row");
	const _component_ui_table_rows = resolveComponent("ui-table-rows");
	const _component_ui_table = resolveComponent("ui-table");
	const _component_ui_tab_trigger = resolveComponent("ui-tab-trigger");
	const _component_ui_tab_list = resolveComponent("ui-tab-list");
	const _component_ui_tab_content = resolveComponent("ui-tab-content");
	const _component_ui_tabs = resolveComponent("ui-tabs");
	const _directive_tooltip = resolveDirective("tooltip");
	return openBlock(), createElementBlock(Fragment, null, [
		createVNode($setup["Head"], { title: _ctx.__("Playground") }, null, 8, ["title"]),
		createVNode(_component_ui_header, {
			title: "Playground",
			icon: "playground",
			class: "starting-style-transition"
		}, {
			default: withCtx(() => [createVNode(_component_ui_subheading, null, {
				default: withCtx(() => [..._cache[0] || (_cache[0] = [createTextVNode("A collection of components to test and play with.", -1)])]),
				_: 1
			})]),
			_: 1
		}),
		createVNode(_component_ui_dropdown, null, {
			trigger: withCtx(() => [createVNode(_component_ui_button, {
				text: "My Account",
				"icon-append": "ui/chevron-vertical",
				class: "[&_svg]:size-2"
			})]),
			default: withCtx(() => [
				createVNode(_component_ui_dropdown_header, {
					text: "My Account",
					icon: "avatar"
				}),
				createVNode(_component_ui_dropdown_menu, null, {
					default: withCtx(() => [
						createVNode(_component_ui_dropdown_item, {
							text: "Photos",
							icon: "assets"
						}),
						createVNode(_component_ui_dropdown_item, {
							text: "Email",
							icon: "mail"
						}),
						createVNode(_component_ui_dropdown_item, {
							text: "Sales",
							icon: "taxonomies"
						})
					]),
					_: 1
				}),
				createVNode(_component_ui_dropdown_footer, {
					text: "Logout",
					icon: "arrow-right"
				})
			]),
			_: 1
		}),
		createBaseVNode("div", _hoisted_1, [_cache[73] || (_cache[73] = createStaticVNode("<div class=\"space-y-12 prose starting-style-transition starting-style-transition-children\"><h1>Typography <strong>Test</strong> Document</h1><p class=\"lead\">This comprehensive document tests all typography elements supported by both tw-prose and @tailwindcss/typography plugins.</p><h2>Headings</h2><h1>Heading Level 1</h1><h2>Heading Level 2</h2><h3>Heading Level 3</h3><h4>Heading Level 4</h4><h5>Heading Level 5</h5><h6>Heading Level 6</h6><h2>Paragraphs and Text Formatting</h2><p>This is a regular paragraph with <strong>bold text</strong>, <em>italic text</em>, <u>underlined text</u>, and <mark>highlighted text</mark>. You can also have <small>small text</small> and text with <sup>superscript</sup> and <sub>subscript</sub>.</p><p>Here&#39;s a paragraph with a <a href=\"#test\">link to test</a> the link styling. Links should have proper hover states and visual distinction from regular text.</p><h2>Lists</h2><h3>Unordered List</h3><ul><li>First item in unordered list</li><li>Second item with <strong>bold text</strong></li><li> Third item with nested list: <ul><li>Nested item 1</li><li>Nested item 2</li></ul></li><li>Fourth item</li></ul><h3>Ordered List</h3><ol><li>First numbered item</li><li>Second numbered item</li><li> Third item with nested ordered list: <ol><li>Nested numbered item 1</li><li>Nested numbered item 2</li></ol></li><li>Fourth numbered item</li></ol><h3>Definition List</h3><dl><dt>Term 1</dt><dd>Definition for term 1 with detailed explanation.</dd><dt>Term 2</dt><dd>Definition for term 2.</dd><dt>Term 3</dt><dd>Another definition with <em>emphasized text</em>.</dd></dl><h2>Blockquotes</h2><blockquote><p>This is a blockquote with a single paragraph. Blockquotes are used to highlight quoted text or important information.</p></blockquote><blockquote><p>This is a blockquote with multiple paragraphs. The first paragraph introduces the quote.</p><p>The second paragraph continues the quoted content and shows how multiple paragraphs are handled within blockquotes.</p><cite> - Author Name, Source Title</cite></blockquote><h2>Code Examples</h2><p>Inline code can be written using <code>console.log(&#39;Hello, World!&#39;)</code> for JavaScript or <code>printf(&quot;Hello, World!&quot;)</code> for C.</p><h3>Code Blocks</h3><pre>                <code>function fibonacci(n) {\n                if (n &lt;= 1) return n;\n                return fibonacci(n - 1) + fibonacci(n - 2);\n                }\n\n                // Usage example\n                console.log(fibonacci(10)); // Output: 55</code>\n            </pre><h3>Keyboard Input</h3><p>To save a file, press <kbd>Ctrl</kbd> + <kbd>S</kbd> on Windows or <kbd>Cmd</kbd> + <kbd>S</kbd> on Mac.</p><h2>Tables</h2><table><thead><tr><th>Name</th><th>Age</th><th>City</th><th>Occupation</th></tr></thead><tbody><tr><td>John Doe</td><td>28</td><td>New York</td><td>Software Developer</td></tr><tr><td>Jane Smith</td><td>32</td><td>San Francisco</td><td>UX Designer</td></tr><tr><td>Bob Johnson</td><td>45</td><td>Chicago</td><td>Project Manager</td></tr></tbody></table><h2>Images and Media</h2><figure><img src=\"https://picsum.photos/600/300\" alt=\"Sample image for testing\"><figcaption>Figure 1: A sample image with caption to test image styling and figure elements.</figcaption></figure><h2>Horizontal Rules</h2><p>Content above the horizontal rule.</p><hr><p>Content below the horizontal rule.</p><h2>Additional Text Elements</h2><p>This paragraph tests various inline elements: <abbr title=\"HyperText Markup Language\">HTML</abbr> abbreviation, <span class=\"time-element\">January 1, 2024</span> time element, and <span>generic span element</span>.</p><address> Contact Information:<br> John Developer<br> 123 Code Street<br> Tech City, TC 12345<br> Email: john@example.com </address><h2>Complex Nested Content</h2><div><h3>Nested Structure Test</h3><p>This section tests how nested content is handled:</p><ol><li>First item with <strong>bold</strong> and <em>italic</em> text</li><li> Second item containing a blockquote: <blockquote><p>Nested blockquote within a list item.</p></blockquote></li><li>Third item with code: <code>nested.code.example()</code></li></ol><p>Final paragraph to close the nested structure test.</p></div><h2>Long Form Content</h2><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p></div><div class=\"space-y-12 prose\"><section class=\"space-y-4\"><p>This is some <code>code</code> in a paragraph.</p><pre>                    <code>$this is some code in a pre tag.\n                    with another line.\n                    </code>\n                </pre></section><section class=\"space-y-4 prose\"><p>This is some <code>code</code> in a paragraph.</p><pre>                    <code>$this is some code in a pre tag.\n                    with another line.\n                    </code>\n                </pre></section></div>", 2)), createBaseVNode("div", _hoisted_2, [
			createBaseVNode("section", _hoisted_3, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[1] || (_cache[1] = [createTextVNode("Badges", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_4, [
				createVNode(_component_ui_badge, {
					size: "lg",
					text: "Green",
					color: "green"
				}),
				createVNode(_component_ui_badge, {
					size: "lg",
					text: "Red",
					color: "red"
				}),
				createVNode(_component_ui_badge, {
					size: "lg",
					text: "Black",
					color: "black"
				}),
				createVNode(_component_ui_badge, {
					text: "Blue",
					color: "blue"
				}),
				createVNode(_component_ui_badge, {
					text: "Amber",
					color: "amber"
				}),
				createVNode(_component_ui_badge, {
					text: "Pink",
					color: "pink"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Cyan",
					color: "cyan"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Purple",
					color: "purple"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Gray",
					color: "gray"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Default"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Rose",
					color: "rose"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Emerald",
					color: "emerald"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Fuchsia",
					color: "fuchsia"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Indigo",
					color: "indigo"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Lime",
					color: "lime"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Orange",
					color: "orange"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Sky",
					color: "sky"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Teal",
					color: "teal"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Violet",
					color: "violet"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "White",
					color: "white"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Yellow",
					color: "yellow"
				})
			])]),
			createBaseVNode("section", _hoisted_5, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[2] || (_cache[2] = [createTextVNode("Badges as Links", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_6, [
				createVNode(_component_ui_badge, {
					size: "lg",
					text: "Green Link",
					color: "green",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "lg",
					text: "Red Link",
					color: "red",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "lg",
					text: "Black Link",
					color: "black",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					text: "Blue Link",
					color: "blue",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					text: "Amber Link",
					color: "amber",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					text: "Pink Link",
					color: "pink",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Cyan Link",
					color: "cyan",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Purple Link",
					color: "purple",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Gray Link",
					color: "gray",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Default Link",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Rose Link",
					color: "rose",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Emerald Link",
					color: "emerald",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Fuchsia Link",
					color: "fuchsia",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Indigo Link",
					color: "indigo",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Lime Link",
					color: "lime",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Orange Link",
					color: "orange",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Sky Link",
					color: "sky",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Teal Link",
					color: "teal",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Violet Link",
					color: "violet",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "White Link",
					color: "white",
					href: "#"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Yellow Link",
					color: "yellow",
					href: "#"
				})
			])]),
			createBaseVNode("section", _hoisted_7, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[3] || (_cache[3] = [createTextVNode("Badges as Buttons", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_8, [
				createVNode(_component_ui_badge, {
					size: "lg",
					text: "Green Button",
					color: "green",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "lg",
					text: "Red Button",
					color: "red",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "lg",
					text: "Black Button",
					color: "black",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					text: "Blue Button",
					color: "blue",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					text: "Amber Button",
					color: "amber",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					text: "Pink Button",
					color: "pink",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Cyan Button",
					color: "cyan",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Purple Button",
					color: "purple",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Gray Button",
					color: "gray",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Default Button",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Rose Button",
					color: "rose",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Emerald Button",
					color: "emerald",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Fuchsia Button",
					color: "fuchsia",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Indigo Button",
					color: "indigo",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Lime Button",
					color: "lime",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Orange Button",
					color: "orange",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Sky Button",
					color: "sky",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Teal Button",
					color: "teal",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Violet Button",
					color: "violet",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "White Button",
					color: "white",
					as: "button"
				}),
				createVNode(_component_ui_badge, {
					size: "sm",
					text: "Yellow Button",
					color: "yellow",
					as: "button"
				})
			])]),
			createBaseVNode("section", _hoisted_9, [
				createVNode(_component_ui_heading, { size: "lg" }, {
					default: withCtx(() => [..._cache[4] || (_cache[4] = [createTextVNode("Buttons", -1)])]),
					_: 1
				}),
				createBaseVNode("div", _hoisted_10, [
					createVNode(_component_ui_button, { text: "Default" }),
					createVNode(_component_ui_button, {
						text: "Primary",
						variant: "primary"
					}),
					createVNode(_component_ui_button, {
						text: "Filled",
						variant: "filled"
					}),
					createVNode(_component_ui_button, {
						text: "Ghost",
						variant: "ghost"
					}),
					createVNode(_component_ui_button, {
						text: "Subtle",
						variant: "subtle"
					})
				]),
				createBaseVNode("div", _hoisted_11, [
					createVNode(_component_ui_button, {
						icon: "save",
						text: "Default"
					}),
					createVNode(_component_ui_button, {
						icon: "save",
						text: "Primary",
						variant: "primary"
					}),
					createVNode(_component_ui_button, {
						icon: "save",
						text: "Danger",
						variant: "danger"
					}),
					createVNode(_component_ui_button, {
						text: "Danger Disabled",
						variant: "danger",
						disabled: ""
					}),
					createVNode(_component_ui_button, {
						text: "Disabled",
						disabled: ""
					}),
					createVNode(_component_ui_button, {
						icon: "save",
						text: "Filled",
						variant: "filled"
					}),
					createVNode(_component_ui_button, {
						icon: "save",
						text: "Ghost",
						variant: "ghost"
					}),
					createVNode(_component_ui_button, {
						icon: "save",
						text: "Subtle",
						variant: "subtle"
					})
				]),
				createBaseVNode("div", _hoisted_12, [
					createVNode(_component_ui_button, { icon: "save" }),
					createVNode(_component_ui_button, {
						icon: "save",
						variant: "primary"
					}),
					createVNode(_component_ui_button, {
						icon: "save",
						variant: "filled"
					}),
					createVNode(_component_ui_button, {
						icon: "save",
						variant: "ghost"
					}),
					createVNode(_component_ui_button, {
						icon: "save",
						variant: "subtle"
					})
				]),
				createBaseVNode("div", _hoisted_13, [createVNode(_component_ui_button_group, null, {
					default: withCtx(() => [
						createVNode(_component_ui_button, { variant: "default" }, {
							default: withCtx(() => [..._cache[5] || (_cache[5] = [createTextVNode("Oldest", -1)])]),
							_: 1
						}),
						createVNode(_component_ui_button, { variant: "default" }, {
							default: withCtx(() => [..._cache[6] || (_cache[6] = [createTextVNode("Newest", -1)])]),
							_: 1
						}),
						createVNode(_component_ui_button, { variant: "default" }, {
							default: withCtx(() => [..._cache[7] || (_cache[7] = [createTextVNode("Top", -1)])]),
							_: 1
						}),
						createVNode(_component_ui_button, { variant: "danger" }, {
							default: withCtx(() => [..._cache[8] || (_cache[8] = [createTextVNode("Danger", -1)])]),
							_: 1
						})
					]),
					_: 1
				})])
			]),
			createBaseVNode("section", _hoisted_14, [
				createVNode(_component_ui_heading, { size: "lg" }, {
					default: withCtx(() => [..._cache[9] || (_cache[9] = [createTextVNode("Button Sizes", -1)])]),
					_: 1
				}),
				createBaseVNode("div", _hoisted_15, [
					createVNode(_component_ui_button, {
						text: "Large",
						size: "lg"
					}),
					createVNode(_component_ui_button, {
						text: "Base",
						size: "base"
					}),
					createVNode(_component_ui_button, {
						text: "Small",
						size: "sm"
					}),
					createVNode(_component_ui_button, {
						text: "Extra Small",
						size: "xs"
					}),
					createVNode(_component_ui_button, {
						text: "2XS",
						size: "2xs"
					})
				]),
				createBaseVNode("div", _hoisted_16, [
					createVNode(_component_ui_button, {
						icon: "save",
						size: "lg"
					}),
					createVNode(_component_ui_button, {
						icon: "save",
						size: "base"
					}),
					createVNode(_component_ui_button, {
						icon: "save",
						size: "sm"
					}),
					createVNode(_component_ui_button, {
						icon: "save",
						size: "xs"
					}),
					createVNode(_component_ui_button, {
						icon: "save",
						size: "2xs"
					})
				])
			]),
			createBaseVNode("section", _hoisted_17, [
				createVNode(_component_ui_heading, { size: "lg" }, {
					default: withCtx(() => [..._cache[10] || (_cache[10] = [createTextVNode("Alerts", -1)])]),
					_: 1
				}),
				createVNode($setup["Alert"], {
					variant: "default",
					text: "This is a default alert message"
				}),
				createVNode($setup["Alert"], {
					variant: "warning",
					text: "This is a warning alert message"
				}),
				createVNode($setup["Alert"], {
					variant: "error",
					text: "This is an error alert message"
				}),
				createVNode($setup["Alert"], {
					variant: "success",
					text: "This is a success alert message"
				}),
				createVNode($setup["Alert"], {
					variant: "warning",
					icon: "git",
					text: "This alert has a custom icon"
				}),
				createVNode($setup["Alert"], {
					variant: "default",
					heading: "Alert Heading",
					text: "This is a default alert message"
				}),
				createVNode($setup["Alert"], {
					variant: "warning",
					heading: "Alert Heading",
					text: "This is a warning alert message"
				}),
				createVNode($setup["Alert"], {
					variant: "error",
					heading: "Alert Heading",
					text: "This is an error alert message"
				}),
				createVNode($setup["Alert"], {
					variant: "success",
					heading: "Alert Heading",
					text: "This is a success alert message"
				}),
				createVNode($setup["Alert"], {
					variant: "warning",
					heading: "Alert Heading",
					icon: "git",
					text: "This alert has a custom icon"
				}),
				createVNode($setup["Alert"], { variant: "success" }, {
					default: withCtx(() => [..._cache[11] || (_cache[11] = [createBaseVNode("strong", null, "Success!", -1), createTextVNode(" This alert uses a slot for custom content. ", -1)])]),
					_: 1
				}),
				createVNode($setup["Alert"], { variant: "warning" }, {
					default: withCtx(() => [..._cache[12] || (_cache[12] = [createBaseVNode("h1", null, "Please run your migrations", -1), createBaseVNode("p", null, [
						createTextVNode("The importer uses Laravel's job batching feature to keep track of the import progress, however, it requires a "),
						createBaseVNode("code", null, "job_batches"),
						createTextVNode(" table in your database. Before you can run the importer, you will need to run "),
						createBaseVNode("code", null, "php artisan migrate"),
						createTextVNode(". This alert uses a heading for the title and a paragraph for the message.")
					], -1)])]),
					_: 1
				}),
				createVNode($setup["Alert"], { variant: "default" }, {
					default: withCtx(() => [..._cache[13] || (_cache[13] = [createBaseVNode("h2", null, "New Feature Available", -1), createBaseVNode("p", null, [
						createTextVNode("We've added support for custom field types. You can now create your own field types by extending the "),
						createBaseVNode("code", null, "Fieldtype"),
						createTextVNode(" class. Check out the documentation for more details.")
					], -1)])]),
					_: 1
				}),
				createVNode($setup["Alert"], { variant: "success" }, {
					default: withCtx(() => [..._cache[14] || (_cache[14] = [createBaseVNode("h3", null, "Backup Completed Successfully", -1), createBaseVNode("p", null, [
						createTextVNode("Your site backup has been created and saved to "),
						createBaseVNode("code", null, "/storage/backups/site-2032-01-15.tar.gz"),
						createTextVNode(". The backup includes all content, assets, and configuration files.")
					], -1)])]),
					_: 1
				}),
				createVNode($setup["Alert"], { variant: "error" }, {
					default: withCtx(() => [..._cache[15] || (_cache[15] = [createBaseVNode("h4", null, "Failed to Connect to Database", -1), createBaseVNode("p", null, [
						createTextVNode("Unable to establish a connection to the database server. Please check your database configuration in "),
						createBaseVNode("code", null, ".env"),
						createTextVNode(" and ensure the database server is running.")
					], -1)])]),
					_: 1
				}),
				_cache[28] || (_cache[28] = createBaseVNode("hr", { class: "my-10" }, null, -1)),
				createVNode($setup["Alert"], { variant: "default" }, {
					default: withCtx(() => [createVNode($setup["Heading"], null, {
						default: withCtx(() => [..._cache[16] || (_cache[16] = [createTextVNode("Using Heading Component", -1)])]),
						_: 1
					}), createVNode($setup["Description"], null, {
						default: withCtx(() => [..._cache[17] || (_cache[17] = [createTextVNode("This alert uses the Heading and Description components instead of native HTML elements. The styles should match the variant colors.", -1)])]),
						_: 1
					})]),
					_: 1
				}),
				createVNode($setup["Alert"], { variant: "warning" }, {
					default: withCtx(() => [createVNode($setup["Heading"], { size: "lg" }, {
						default: withCtx(() => [..._cache[18] || (_cache[18] = [createTextVNode("Warning: Action Required", -1)])]),
						_: 1
					}), createVNode($setup["Description"], null, {
						default: withCtx(() => [..._cache[19] || (_cache[19] = [
							createTextVNode("This is a heading size ", -1),
							createBaseVNode("code", null, "lg", -1),
							createTextVNode(" example, a warning alert with a larger heading. The Heading component supports different sizes and should inherit the alert's color scheme.", -1)
						])]),
						_: 1
					})]),
					_: 1
				}),
				createVNode($setup["Alert"], { variant: "success" }, {
					default: withCtx(() => [createVNode($setup["Heading"], { size: "xl" }, {
						default: withCtx(() => [..._cache[20] || (_cache[20] = [createTextVNode("Backup Completed Successfully", -1)])]),
						_: 1
					}), createVNode($setup["Description"], null, {
						default: withCtx(() => [..._cache[21] || (_cache[21] = [
							createTextVNode("Your site backup has been created and saved to ", -1),
							createBaseVNode("a", { href: "https://statamic.dev" }, [createBaseVNode("code", null, "/storage/backups/site-2032-01-15.tar.gz")], -1),
							createTextVNode(". The backup includes all content, assets, and configuration files.", -1)
						])]),
						_: 1
					})]),
					_: 1
				}),
				createVNode($setup["Alert"], { variant: "success" }, {
					default: withCtx(() => [createVNode($setup["Heading"], { size: "2xl" }, {
						default: withCtx(() => [..._cache[22] || (_cache[22] = [createTextVNode("Backup Completed Very Successfully", -1)])]),
						_: 1
					}), createVNode($setup["Description"], null, {
						default: withCtx(() => [..._cache[23] || (_cache[23] = [
							createTextVNode("Such a massive heading isn't recommended, but here it is for testing purposes. Your site backup has been created and saved to ", -1),
							createBaseVNode("a", { href: "https://statamic.dev" }, [createBaseVNode("code", null, "/storage/backups/site-2032-01-15.tar.gz")], -1),
							createTextVNode(". The backup includes all content, assets, and configuration files.", -1)
						])]),
						_: 1
					})]),
					_: 1
				}),
				createVNode($setup["Alert"], { variant: "error" }, {
					default: withCtx(() => [createVNode($setup["Heading"], { level: "2" }, {
						default: withCtx(() => [..._cache[24] || (_cache[24] = [createTextVNode("Database Connection Failed", -1)])]),
						_: 1
					}), createVNode($setup["Description"], null, {
						default: withCtx(() => [..._cache[25] || (_cache[25] = [
							createTextVNode("This is a heading level ", -1),
							createBaseVNode("code", null, "2", -1),
							createTextVNode(" example, with no heading size difference. Unable to establish a connection to the database server. Please check your database configuration in ", -1),
							createBaseVNode("code", null, ".env", -1),
							createTextVNode(" and ensure the database server is running.", -1)
						])]),
						_: 1
					})]),
					_: 1
				}),
				createVNode($setup["Alert"], { variant: "warning" }, {
					default: withCtx(() => [createVNode($setup["Heading"], null, {
						default: withCtx(() => [..._cache[26] || (_cache[26] = [createTextVNode("Migration Required", -1)])]),
						_: 1
					}), createVNode($setup["Description"], null, {
						default: withCtx(() => [..._cache[27] || (_cache[27] = [
							createTextVNode("The importer uses Laravel's job batching feature to keep track of the import progress, however, it requires a ", -1),
							createBaseVNode("code", null, "job_batches", -1),
							createTextVNode(" table in your database. Before you can run the importer, you will need to run ", -1),
							createBaseVNode("code", null, "php artisan migrate", -1),
							createTextVNode(".", -1)
						])]),
						_: 1
					})]),
					_: 1
				})
			]),
			createBaseVNode("section", _hoisted_18, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[29] || (_cache[29] = [createTextVNode("Calendar", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_19, [createVNode(_component_ui_card, null, {
				default: withCtx(() => [createVNode(_component_ui_calendar)]),
				_: 1
			}), createVNode(_component_ui_card, null, {
				default: withCtx(() => [createVNode(_component_ui_calendar, { "number-of-months": 2 })]),
				_: 1
			})])]),
			createBaseVNode("section", _hoisted_20, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[30] || (_cache[30] = [createTextVNode("Card", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_21, [createVNode(_component_ui_card, { class: "w-80 space-y-6" }, {
				default: withCtx(() => [
					createBaseVNode("header", null, [createVNode(_component_ui_heading, { size: "lg" }, {
						default: withCtx(() => [..._cache[31] || (_cache[31] = [createTextVNode("Create a new account", -1)])]),
						_: 1
					}), createVNode(_component_ui_subheading, null, {
						default: withCtx(() => [..._cache[32] || (_cache[32] = [createTextVNode("Welcome to the thing! You're gonna love it here.", -1)])]),
						_: 1
					})]),
					createVNode(_component_ui_input, {
						label: "Name",
						placeholder: "Your name"
					}),
					createVNode(_component_ui_input, {
						label: "Email",
						type: "email",
						placeholder: "Your email"
					}),
					createBaseVNode("div", _hoisted_22, [createVNode(_component_ui_button, {
						variant: "primary",
						class: "w-full",
						text: "Continue",
						type: "submit"
					}), createVNode(_component_ui_button, {
						variant: "ghost",
						class: "w-full"
					}, {
						default: withCtx(() => [..._cache[33] || (_cache[33] = [createTextVNode("Already have an account? Go sign in", -1)])]),
						_: 1
					})])
				]),
				_: 1
			})])]),
			createBaseVNode("section", _hoisted_23, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[34] || (_cache[34] = [createTextVNode("Card Panel", -1)])]),
				_: 1
			}), createVNode(_component_ui_card_panel, { heading: "Card Panel" }, {
				default: withCtx(() => [..._cache[35] || (_cache[35] = [createTextVNode("This is a card panel.", -1)])]),
				_: 1
			})]),
			createBaseVNode("section", _hoisted_24, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[36] || (_cache[36] = [createTextVNode("Checkboxes", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_25, [createVNode(_component_ui_checkbox_group, {
				name: "meals",
				label: "Select your favorite meals"
			}, {
				default: withCtx(() => [
					createVNode(_component_ui_checkbox, {
						label: "Breakfast",
						description: "The morning meal. Should include eggs.",
						value: "breakfast"
					}),
					createVNode(_component_ui_checkbox, {
						label: "Lunch",
						description: "The mid-day meal. Should be protein heavy.",
						value: "lunch"
					}),
					createVNode(_component_ui_checkbox, {
						label: "Dinner",
						description: "The evening meal. Should be delicious.",
						value: "dinner"
					})
				]),
				_: 1
			})])]),
			createBaseVNode("section", _hoisted_26, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[37] || (_cache[37] = [createTextVNode("Datepicker", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_27, [createVNode(_component_ui_date_picker)])]),
			createBaseVNode("section", _hoisted_28, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[38] || (_cache[38] = [createTextVNode("Dropdown", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_29, [createVNode(_component_ui_dropdown, null, {
				trigger: withCtx(() => [createVNode(_component_ui_button, {
					text: "Do a Action",
					variant: "filled",
					"icon-append": "chevron-vertical",
					class: "[&_svg]:size-2"
				})]),
				default: withCtx(() => [createVNode(_component_ui_dropdown_menu, null, {
					default: withCtx(() => [
						createVNode(_component_ui_dropdown_item, { text: "Bake a food" }),
						createVNode(_component_ui_dropdown_item, { text: "Write that book" }),
						createVNode(_component_ui_dropdown_item, { text: "Eat this meal" }),
						createVNode(_component_ui_dropdown_item, { text: "Lie about larceny" }),
						createVNode(_component_ui_dropdown_item, { text: "Save some bird" })
					]),
					_: 1
				})]),
				_: 1
			})])]),
			createBaseVNode("section", _hoisted_30, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[39] || (_cache[39] = [createTextVNode("Icons", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_31, [(openBlock(true), createElementBlock(Fragment, null, renderList($props.icons, (icon) => {
				return openBlock(), createElementBlock("div", _hoisted_32, [createVNode(_component_ui_icon, {
					name: icon,
					class: "size-6"
				}, null, 8, ["name"]), createBaseVNode("span", _hoisted_33, toDisplayString(icon), 1)]);
			}), 256))])]),
			createBaseVNode("section", _hoisted_34, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[40] || (_cache[40] = [createTextVNode("Input", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_35, [
				createVNode(_component_ui_input, {
					name: "email",
					type: "email",
					required: "",
					label: "Email",
					value: "Edit me"
				}),
				createVNode(_component_ui_input, {
					name: "email",
					type: "email",
					required: "",
					label: "Email",
					"read-only": "",
					value: "Read only. Tab, select, but not edit."
				}),
				createVNode(_component_ui_input, {
					name: "email",
					type: "email",
					required: "",
					label: "Email",
					disabled: "",
					value: "Disabled. Cant touch me."
				})
			])]),
			createBaseVNode("section", _hoisted_36, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[41] || (_cache[41] = [createTextVNode("Modal", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_37, [createVNode(_component_ui_modal, { title: "That's Pretty Neat" }, {
				trigger: withCtx(() => [createVNode(_component_ui_button, { text: "How neat is that?" })]),
				default: withCtx(() => [_cache[42] || (_cache[42] = createBaseVNode("p", null, "This is some very basic example text to show you what stacks look like with content.", -1))]),
				_: 1
			})])]),
			createBaseVNode("section", _hoisted_38, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[43] || (_cache[43] = [createTextVNode("Stack", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_39, [createVNode(_component_ui_stack, { title: "That's Pretty Neat" }, {
				trigger: withCtx(() => [createVNode(_component_ui_button, { text: "How neat is that?" })]),
				default: withCtx(() => [_cache[44] || (_cache[44] = createBaseVNode("p", null, "This is some very basic example text to show you what stacks look like with content.", -1))]),
				_: 1
			})])]),
			createBaseVNode("section", _hoisted_40, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[45] || (_cache[45] = [createTextVNode("Popover", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_41, [createVNode(_component_ui_popover, null, {
				trigger: withCtx(() => [createVNode(_component_ui_button, { text: "Open Popover" })]),
				default: withCtx(() => [createBaseVNode("div", _hoisted_42, [
					createVNode(_component_ui_heading, { text: "Provide Feedback" }),
					createVNode(_component_ui_textarea, {
						placeholder: "How we can make this component better?",
						elastic: ""
					}),
					createBaseVNode("div", _hoisted_43, [createVNode(_component_ui_button, {
						variant: "primary",
						size: "sm",
						text: "Submit"
					})])
				])]),
				_: 1
			})])]),
			createBaseVNode("section", _hoisted_44, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[46] || (_cache[46] = [createTextVNode("Radio Group", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_45, [createVNode(_component_ui_radio_group, {
				name: "favorite",
				label: "Choose your favorite meal"
			}, {
				default: withCtx(() => [
					createVNode(_component_ui_radio, {
						label: "Breakfast",
						description: "The morning meal. Should include eggs.",
						value: "breakfast",
						checked: ""
					}),
					createVNode(_component_ui_radio, {
						label: "Lunch",
						description: "The mid-day meal. Should be protein heavy.",
						value: "lunch"
					}),
					createVNode(_component_ui_radio, {
						label: "Dinner",
						description: "The evening meal Should be delicious.",
						value: "dinner"
					})
				]),
				_: 1
			})])]),
			createBaseVNode("section", _hoisted_46, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[47] || (_cache[47] = [createTextVNode("Disabled Radio Items", -1)])]),
				_: 1
			}), createVNode(_component_ui_radio_group, {
				name: "favorite",
				label: "Choose your favorite Star Wars movie"
			}, {
				default: withCtx(() => [
					createVNode(_component_ui_radio_item, {
						label: "A New Hope",
						value: "ep4"
					}),
					createVNode(_component_ui_radio_item, {
						label: "Empire Strikes Back",
						value: "ep5"
					}),
					createVNode(_component_ui_radio_item, {
						label: "Return of the Jedi",
						value: "ep6"
					}),
					createVNode(_component_ui_radio_item, {
						disabled: "",
						label: "the Force Awakens",
						value: "ep7"
					}),
					createVNode(_component_ui_radio_item, {
						disabled: "",
						label: "The Last Jedi",
						value: "ep8"
					}),
					createVNode(_component_ui_radio_item, {
						disabled: "",
						label: "The Rise of Skywalker",
						value: "ep9"
					})
				]),
				_: 1
			})]),
			createBaseVNode("section", _hoisted_47, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[48] || (_cache[48] = [createTextVNode("Select", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_48, [createVNode(_component_ui_select, {
				icon: "money-bag-dollar",
				class: "w-full",
				label: "Favorite band",
				options: [
					{
						label: "The Midnight",
						value: "the_midnight"
					},
					{
						label: "The 1975",
						value: "the_1975"
					},
					{
						label: "Sunglasses Kid",
						value: "sunglasses_kid"
					}
				]
			})])]),
			createBaseVNode("section", _hoisted_49, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[49] || (_cache[49] = [createTextVNode("Separator", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_50, [createVNode(_component_ui_separator, { text: "vs" })])]),
			createBaseVNode("section", _hoisted_51, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[50] || (_cache[50] = [createTextVNode("Splitter", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_52, [createVNode(_component_ui_splitter_group, null, {
				default: withCtx(() => [
					createVNode(_component_ui_splitter_panel, { class: "flex h-24 items-center justify-center rounded-xl bg-white" }, {
						default: withCtx(() => [..._cache[51] || (_cache[51] = [createTextVNode(" Left ", -1)])]),
						_: 1
					}),
					createVNode(_component_ui_splitter_resize_handle, { class: "w-3" }),
					createVNode(_component_ui_splitter_panel, { class: "flex h-24 items-center justify-center rounded-xl bg-white" }, {
						default: withCtx(() => [..._cache[52] || (_cache[52] = [createTextVNode(" Right ", -1)])]),
						_: 1
					})
				]),
				_: 1
			})])]),
			createBaseVNode("section", _hoisted_53, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[53] || (_cache[53] = [createTextVNode("Switch", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_54, [
				createVNode(_component_ui_switch, { size: "sm" }),
				createVNode(_component_ui_switch),
				createVNode(_component_ui_switch, { size: "lg" })
			])]),
			createBaseVNode("section", _hoisted_55, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[54] || (_cache[54] = [createTextVNode("Table", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_56, [createVNode(_component_ui_table, null, {
				default: withCtx(() => [createVNode(_component_ui_table_columns, null, {
					default: withCtx(() => [
						createVNode(_component_ui_table_column, null, {
							default: withCtx(() => [..._cache[55] || (_cache[55] = [createTextVNode("Product", -1)])]),
							_: 1
						}),
						createVNode(_component_ui_table_column, null, {
							default: withCtx(() => [..._cache[56] || (_cache[56] = [createTextVNode("Stock", -1)])]),
							_: 1
						}),
						createVNode(_component_ui_table_column, { class: "text-right" }, {
							default: withCtx(() => [..._cache[57] || (_cache[57] = [createTextVNode("Price", -1)])]),
							_: 1
						})
					]),
					_: 1
				}), createVNode(_component_ui_table_rows, null, {
					default: withCtx(() => [
						createVNode(_component_ui_table_row, null, {
							default: withCtx(() => [
								createVNode(_component_ui_table_cell, null, {
									default: withCtx(() => [..._cache[58] || (_cache[58] = [createTextVNode("Mechanical Keyboard", -1)])]),
									_: 1
								}),
								createVNode(_component_ui_table_cell, null, {
									default: withCtx(() => [createVNode(_component_ui_badge, {
										color: "green",
										pill: ""
									}, {
										default: withCtx(() => [..._cache[59] || (_cache[59] = [createTextVNode("In Stock", -1)])]),
										_: 1
									})]),
									_: 1
								}),
								createVNode(_component_ui_table_cell, { class: "text-right font-semibold text-gray-925" }, {
									default: withCtx(() => [..._cache[60] || (_cache[60] = [createTextVNode("$159.00", -1)])]),
									_: 1
								})
							]),
							_: 1
						}),
						createVNode(_component_ui_table_row, null, {
							default: withCtx(() => [
								createVNode(_component_ui_table_cell, null, {
									default: withCtx(() => [..._cache[61] || (_cache[61] = [createTextVNode("Ergonomic Mouse", -1)])]),
									_: 1
								}),
								createVNode(_component_ui_table_cell, null, {
									default: withCtx(() => [createVNode(_component_ui_badge, {
										color: "red",
										pill: ""
									}, {
										default: withCtx(() => [..._cache[62] || (_cache[62] = [createTextVNode("Out of Stock", -1)])]),
										_: 1
									})]),
									_: 1
								}),
								createVNode(_component_ui_table_cell, { class: "text-right font-semibold text-gray-925" }, {
									default: withCtx(() => [..._cache[63] || (_cache[63] = [createTextVNode("$89.00", -1)])]),
									_: 1
								})
							]),
							_: 1
						}),
						createVNode(_component_ui_table_row, null, {
							default: withCtx(() => [
								createVNode(_component_ui_table_cell, null, {
									default: withCtx(() => [..._cache[64] || (_cache[64] = [createTextVNode("4K Monitor", -1)])]),
									_: 1
								}),
								createVNode(_component_ui_table_cell, null, {
									default: withCtx(() => [createVNode(_component_ui_badge, {
										color: "yellow",
										pill: ""
									}, {
										default: withCtx(() => [..._cache[65] || (_cache[65] = [createTextVNode("Low Stock", -1)])]),
										_: 1
									})]),
									_: 1
								}),
								createVNode(_component_ui_table_cell, { class: "text-right font-semibold text-gray-925" }, {
									default: withCtx(() => [..._cache[66] || (_cache[66] = [createTextVNode("$349.00", -1)])]),
									_: 1
								})
							]),
							_: 1
						})
					]),
					_: 1
				})]),
				_: 1
			})])]),
			createBaseVNode("section", _hoisted_57, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[67] || (_cache[67] = [createTextVNode("Tabs", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_58, [createVNode(_component_ui_tabs, {
				"default-tab": "tab1",
				class: "w-full"
			}, {
				default: withCtx(() => [
					createVNode(_component_ui_tab_list, null, {
						default: withCtx(() => [
							createVNode(_component_ui_tab_trigger, {
								text: "Shiny",
								name: "tab1"
							}),
							createVNode(_component_ui_tab_trigger, {
								text: "Happy",
								name: "tab2"
							}),
							createVNode(_component_ui_tab_trigger, {
								text: "People",
								name: "tab3"
							})
						]),
						_: 1
					}),
					createVNode(_component_ui_tab_content, { name: "tab1" }, {
						default: withCtx(() => [..._cache[68] || (_cache[68] = [createBaseVNode("p", { class: "py-8" }, "Tab 1 content", -1)])]),
						_: 1
					}),
					createVNode(_component_ui_tab_content, { name: "tab2" }, {
						default: withCtx(() => [..._cache[69] || (_cache[69] = [createBaseVNode("p", { class: "py-8" }, "Tab 2 content", -1)])]),
						_: 1
					}),
					createVNode(_component_ui_tab_content, { name: "tab3" }, {
						default: withCtx(() => [..._cache[70] || (_cache[70] = [createBaseVNode("p", { class: "py-8" }, "Tab 3 content", -1)])]),
						_: 1
					})
				]),
				_: 1
			})])]),
			createBaseVNode("section", _hoisted_59, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[71] || (_cache[71] = [createTextVNode("Textarea", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_60, [createVNode(_component_ui_textarea, { label: "Message" })])]),
			createBaseVNode("section", _hoisted_61, [createVNode(_component_ui_heading, { size: "lg" }, {
				default: withCtx(() => [..._cache[72] || (_cache[72] = [createTextVNode("Tooltip", -1)])]),
				_: 1
			}), createBaseVNode("div", _hoisted_62, [withDirectives(createVNode(_component_ui_button, { text: "Hover me" }, null, 512), [[_directive_tooltip, "Never gonna give you up"]])])])
		])])
	], 64);
}
var Playground_default = /*#__PURE__*/ _plugin_vue_export_helper_default(_sfc_main, [["render", _sfc_render], ["__file", "Playground.vue"]]);
//#endregion
export { Playground_default as default };
