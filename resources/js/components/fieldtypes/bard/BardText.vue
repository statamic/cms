<template>

    <div class="bard-block bard-text" :class="{
        'divider-at-start': dividerAtStart,
        'divider-at-end': dividerAtEnd
    }">

        <div class="bard-set-selector" v-show="hasSets && isShowingOptions" :style="optionStyles">
            <dropdown-list>
                <button type="button" class="btn btn-round" slot="trigger">
                    <span class="icon icon-plus text-grey-80 antialiased"></span>
                </button>
                <ul class="dropdown-menu">
                    <li v-for="set in $parent.config.sets">
                        <a @click.prevent="insertSet(set.handle)">
                            <i class="icon icon-add-to-list"></i>
                            {{ set.display || set.handle }}
                        </a>
                    </li>
                    <li v-if="isBlank">
                        <a @click.prevent="$emit('deleted', index)">
                            <i class="icon icon-trash"></i> {{ __('Delete') }}
                        </a>
                    </li>
                </ul>
            </dropdown-list>
        </div>

        <textarea
            class="bard-source"
            v-model="text"
            v-show="showSource"
            ref="source"
            rows="1"
        ></textarea>

        <div
            class="bard-editor"
            :class="style"
            v-show="!showSource"
            ref="input"
            :spellcheck="spellcheckEnabled"
        ></div>

        <selector v-if="showAssetSelector"
                  :container="container"
                  :folder="folder"
                  :selected="selectedAssets"
                  :restrict-container-navigation="restrictAssetNavigation"
                  :restrict-folder-navigation="restrictAssetNavigation"
                  :max-files="1"
                  @selected="assetsSelected"
                  @closed="closeAssetSelector"
        ></selector>

    </div>

</template>


<script>

    import ScribeEditor from 'scribe-editor';
    import ScribeToolbar from 'scribe-plugin-toolbar';
    import ScribeHeadingCommand from 'scribe-plugin-heading-command';
    import ScribeBlockquoteCommand from 'scribe-plugin-blockquote-command';
    import ScribeSanitizer from 'scribe-plugin-sanitizer';
    import ScribeCodeCommand from 'scribe-plugin-code-command';
    import ScribePluginSmartLists from 'scribe-plugin-smart-lists';
    import ScribePluginFormatterHtmlEnsureSemanticElements from 'scribe-plugin-formatter-html-ensure-semantic-elements';
    import ScribeAssetCommand from './AssetCommand';
    import ScribeLinkTooltip from './LinkTooltip';
    import ScribePluginAutoHr from './AutoHr';
    import ScribePluginAutoBlockquote from './AutoBlockquote';
    import autosize from 'autosize';
    import InsertsAssets from '../InsertsAssets';

    export default {

        name: 'BardText',

        components: {
            selector: require('../../assets/Selector.vue')
        },

        mixins: [InsertsAssets],

        props: ['values', 'index', 'showSource', 'dividerAtStart', 'dividerAtEnd'],

        data() {
            return {
                editor: null,
                isShowingOptions: false,
                optionsTopPosition: 0,
                focusedElement: null,
                dropped: { sibling: null, position: null },
                text: this.values.text || '',
                style: this.$parent.config.style || 'sans'
            };
        },

        computed: {

            field() {
                return this.$refs.input
            },

            sourceField() {
                return this.$refs.source;
            },

            isBlank() {
                return this.text === '' || this.text === '<p><br></p>';
            },

            optionStyles() {
                return {
                    top: this.optionsTopPosition + 'px'
                }
            },

            hasSets() {
                return this.$parent.hasSets;
            },

            spellcheckEnabled() {
                const spellcheck = this.$parent.config.spellcheck;

                // Spellcheck will only be disabled if it has been explicitly set to false.
                // If the config value doesn't exist, we will default to being enabled.
                return (spellcheck === false) ? false : true;
            }

        },

        watch: {

            showSource(show) {
                if (show) {
                    this.$nextTick(() => { autosize.update(this.sourceField) });
                } else {
                    this.updateEditorHtml(this.text);
                }
            },

            text(text) {
                this.$emit('text-updated', this.index, text);
            },

            'values.text': function (text, oldText) {
                // Prevent an update when typing directly in the field.
                if (text === this.text) return;

                this.text = text;
                this.$nextTick(() => this.updateEditorHtml(text));
            }

        },

        mounted() {
            autosize(this.sourceField);

            this.initScribe();

            this.removeDropAreas();
        },

        methods: {

            /**
             * Used by the InsertsAssets mixin to get the config.
             */
            getFieldtypeConfig() {
                return this.$parent.config;
            },

            plainText() {
                return this.editor.el.textContent;
            },

            addDropAreas() {
                // Prevent scribe from cleaning up the dom, therefore removing the divs we're about to add.
                this.editor._skipFormatters = true;

                const editor = this.editor.el;

                let firstAdded = false;

                Array.from(editor.children).forEach(child => {
                    if (child.classList.contains('bard-drop-area')) return;

                    if (! firstAdded) {
                        this.addDropAreaBefore(child, editor);
                        firstAdded = true;
                    }

                    this.addDropAreaAfter(child, editor);
                });

                this.$nextTick(() => this.addDropAreaMouseoverListeners(editor.children));
            },

            addDropAreaBefore(child, editor) {
                let newNode = document.createElement('div');
                let childNode = document.createElement('div');
                childNode.className += 'bard-drop-area-inner';
                newNode.appendChild(childNode);
                newNode.className += 'bard-drop-area bard-drop-area-before';
                editor.insertBefore(newNode, child);
            },

            addDropAreaAfter(child, editor) {
                let newNode = document.createElement('div');
                let childNode = document.createElement('div');
                childNode.className += 'bard-drop-area-inner';
                newNode.appendChild(childNode);
                newNode.className += 'bard-drop-area bard-drop-area-after';
                editor.insertBefore(newNode, child.nextSibling);
            },

            addDropAreaMouseoverListeners(children) {
                Array.from(children).forEach(child => {
                    if (! child.classList.contains('bard-drop-area')) return;

                    if (child.classList.contains('bard-drop-area-before')) {
                        child.children[0].addEventListener('mouseover', () => {
                            this.dropped.sibling = child.nextElementSibling;
                            this.dropped.position = 'previous';
                        });
                    } else {
                        child.children[0].addEventListener('mouseover', () => {
                            this.dropped.sibling = child.previousElementSibling;
                            this.dropped.position = 'next';
                        });
                    }
                });
            },

            removeDropAreas() {
                const els = this.editor.el.getElementsByClassName('bard-drop-area');
                Array.from(els).forEach(el => el.remove());
                this.text = this.editor.getContent();
            },
            initScribe() {
                this.editor = new ScribeEditor(this.field);

                this.editor.use(ScribeHeadingCommand(1));
                this.editor.use(ScribeHeadingCommand(2));
                this.editor.use(ScribeHeadingCommand(3));
                this.editor.use(ScribeHeadingCommand(4));
                this.editor.use(ScribeHeadingCommand(5));
                this.editor.use(ScribeHeadingCommand(6));
                this.editor.use(ScribeBlockquoteCommand());
                this.editor.use(ScribeAssetCommand(this));
                this.editor.use(ScribePluginSmartLists());
                this.editor.use(ScribePluginAutoHr());
                this.editor.use(ScribePluginAutoBlockquote());
                this.editor.use(ScribeSanitizer(Statamic.$config.get('bard').sanitizer || {
                    tags: {
                        p: {},
                        br: {},
                        b: {},
                        strong: {},
                        i: {},
                        strike: {},
                        blockquote: {},
                        code: {},
                        ol: {},
                        ul: {},
                        li: {},
                        a: { href: true, target: true, rel: true },
                        h1: {},
                        h2: {},
                        h3: {},
                        h4: {},
                        h5: {},
                        h6: {},
                        u: {},
                        sup: {},
                        sub: {},
                        hr: {},
                    }
                }));

                if (!this.$parent.config.markdown) {
                    this.editor.use(ScribeToolbar(this.$parent.$refs.toolbar, { shared: true }));
                    this.editor.use(ScribeLinkTooltip(this.$parent.$refs.linkToolbar));
                }

                // Disable cmd+b and cmd+i when in markdown mode, as this would change the html.
                if (this.$parent.config.markdown) {
                    this.editor.el.addEventListener('keydown', (e) => {
                        if (e.metaKey && (e.keyCode === 66 || e.keyCode === 73)) e.preventDefault();
                    });
                }

                this.editor.use(ScribeCodeCommand());

                if (this.$parent.config.semantic_elements) {
                    this.editor.use(ScribePluginFormatterHtmlEnsureSemanticElements());
                }

                Statamic.$config.get('bard').plugins.forEach(plugin => this.editor.use(plugin.call()));

                this.editor.on('content-changed', () => {
                    this.text = this.editor.getHTML();
                });

                this.editor.el.addEventListener('keyup', (e) => {
                    const selection = new this.editor.api.Selection().selection;
                    if (selection.focusNode != this.editor.el) {
                        let el = this.getParentElement(selection.focusNode);
                        this.focusElement(el);
                    }
                });

                this.editor.el.addEventListener('keyup', (e) => {
                    this.$emit('selection-changed');
                });

                this.editor.el.addEventListener('keydown', (e) => {
                    const isUp = e.key === 'ArrowUp' || e.key === 'ArrowLeft';
                    const isDown = e.key === 'ArrowDown' || e.key === 'ArrowRight';

                    if (!isUp && !isDown) return;

                    const selection = new this.editor.api.Selection();

                    // We only care about caret movements. Ranges imply that you are selecting text, so we
                    // shouln't attempt to go beyond the text. That'll just be confusing.
                    if (selection.selection.type === 'Range') return;

                    const selectedElement = this.getParentElement(selection.selection.focusNode);
                    const isInFirstElement = !selectedElement.previousElementSibling;
                    const isInLastElement = !selectedElement.nextElementSibling;

                    if (isUp && isInFirstElement && selection.range.startOffset === 0) {
                        this.$emit('arrow-up-at-start', this.index);
                    } else if (isDown && isInLastElement) {
                        let caretPosition = this.getCaretPositionInElement(selectedElement);
                        if (caretPosition === selectedElement.textContent.length) {
                            this.$emit('arrow-down-at-end', this.index);
                        }
                    }
                });

                this.editor.el.addEventListener('click', (e) => {
                    if (e.target === this.editor.el) return;

                    let el = this.getParentElement(e.target);
                    this.focusElement(el);
                });

                this.editor.el.addEventListener('mousedown', () => {
                    const listener = () => {
                        setTimeout(() => { this.$emit('selection-changed') }, 1);
                        window.removeEventListener('mouseup', listener);
                    };
                    window.addEventListener('mouseup', listener);
                });

                this.updateEditorHtml(this.text);
            },

            moveOptionsToElement(el) {
                this.isShowingOptions = true;
                this.optionsTopPosition = el.offsetTop - 2;
            },

            hideOptions() {
                this.isShowingOptions = false;
            },

            elementIsEmpty(el) {
                return el.textContent == '';
            },

            focusElement(el) {
                this.focusedElement = el;

                // Only allow p tags to have the + shown inside them.
                // This is a workaround for an issue where if the entire paragraph is bold, hitting enter
                // to start a new paragraph would place you inside a b tag, then when inserting a set
                // it would delete the previous paragraph. Complicated to explain. More so to fix.
                if (el.nodeName !== 'P') {
                    return this.hideOptions();
                }

                return this.elementIsEmpty(el) ? this.moveOptionsToElement(el) : this.hideOptions();
            },

            getNextSiblings(el) {
                var siblings = [];
                while (el = el.nextSibling) {
                    siblings.push(el);
                }
                return siblings;
            },

            getPreviousSiblings(el) {
                var siblings = [];
                while (el = el.previousSibling) {
                    siblings.push(el);
                }
                return siblings;
            },

            focus() {
                this.focusAtStart();
            },

            focusAtStart() {
                this.setCaretBefore(this.editor.el.children[0]);
            },

            focusAtEnd() {
                const lastElement = this.editor.el.children[this.editor.el.children.length-1];
                this.setCaretAfter(lastElement);
            },

            setCaretBefore(el) {
                const selection = new this.editor.api.Selection();
                const range = selection.range;
                range.selectNode(el);
                selection.selection.removeAllRanges();
                selection.selection.addRange(range);
            },

            setCaretAfter(el) {
                const placeholder = document.createElement('span');
                el.appendChild(placeholder);

                const selection = new this.editor.api.Selection();
                const range = selection.range;
                range.selectNode(placeholder);
                selection.selection.removeAllRanges();
                selection.selection.addRange(range);

                placeholder.remove();
            },

            insertSet(type) {
                const [before, after] = this.getBeforeAndAfterHtml();

                this.$emit('set-inserted', type, this.index, before, after);
            },

            getBeforeAndAfterHtml() {
                const before = this.getHtmlFromElements(this.getPreviousSiblings(this.focusedElement).reverse());
                const after = this.getHtmlFromElements(this.getNextSiblings(this.focusedElement));

                return [before, after];
            },

            getHtmlFromElements(els) {
                return _.reduce(els, (carry, el) => {
                    const html = el.outerHTML || '';
                    return carry + html;
                }, '');
            },

            insertParagraph() {
                let nextNode = this.dropped.sibling;

                if (this.dropped.position === 'next') {
                    nextNode = nextNode.nextSibling;
                }

                // Place a new paragraph before the "next" one.
                let newNode = document.createElement('p');
                this.field.insertBefore(newNode, nextNode);

                // Bring the + options to the new paragraph.
                this.focusElement(newNode);
            },

            updateEditorHtml() {
                this.editor.setContent(this.text);
            },

            assetsSelected(assets) {
                // Loop over returned assets, even though there will only be one.
                this.$http.post(cp_url('assets/get'), { assets }, (response) => {
                    _(response).each(asset => {
                        var selection = new this.editor.api.Selection();
                        selection.selectMarkers();
                        this.editor.el.focus();
                        this.editor.getCommand('createLink').execute(asset.url);
                    });
                });
            },

            closeAssetSelector() {
                this.showAssetSelector = false;

                var selection = new this.editor.api.Selection();
                selection.selectMarkers();
            },

            // Takes an element from within a contenteditable, and returns the top-most
            // parent element as a direct child of the editor div.
            // For example: <p><b><i>foo</i></b></p>
            // If you give the foo text node, it will return the p tag.
            getParentElement(el) {
                while (el.parentNode != this.editor.el) {
                    el = el.parentNode;
                }
                return el;
            },

            getCaretPositionInElement(element) {
                let range = new this.editor.api.Selection().range;
                let preCaretRange = range.cloneRange();
                preCaretRange.selectNodeContents(element);
                preCaretRange.setEnd(range.endContainer, range.endOffset);
                return preCaretRange.toString().length;
            }

        }

    }

</script>
