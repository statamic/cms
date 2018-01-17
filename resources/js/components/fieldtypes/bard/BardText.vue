<template>

    <div class="bard-block bard-text">

        <div class="bard-set-selector" v-show="hasSets && isShowingOptions" :style="optionStyles">
            <div class="blerp">
                <button type="button" class="btn btn-round dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="icon icon-plus"></span>
                </button>
                <ul class="dropdown-menu">
                    <li v-for="set in $parent.config.sets">
                        <a @click.prevent="insertSet(set.name)">
                            <i class="icon icon-add-to-list"></i>
                            {{ set.display || set.name }}
                        </a>
                    </li>
                    <li v-if="isBlank">
                        <a @click.prevent="$emit('deleted', index)">
                            <i class="icon icon-trash"></i> {{ translate('cp.delete') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <textarea
            class="bard-source"
            v-model="text"
            v-show="showSource"
            v-el:source
            rows="1"
        ></textarea>

        <div
            class="bard-editor"
            v-show="!showSource"
            v-el:input
        ></div>

        <selector v-if="showAssetSelector"
                  :container="container"
                  :folder="folder"
                  :selected="selectedAssets"
                  :restrict-navigation="restrictAssetNavigation"
                  :max-files="1"
                  @selected="assetsSelected"
                  @closed="closeAssetSelector"
        ></selector>

    </div>

</template>


<script>

    import AutoList from './AutoList';
    import InsertsAssets from '../InsertsAssets';

    export default {

        name: 'BardText',

        components: {
            selector: require('../../assets/Selector.vue')
        },

        mixins: [InsertsAssets],

        props: ['data', 'index', 'showSource'],

        data() {
            return {
                editor: null,
                isShowingOptions: false,
                optionsTopPosition: 0,
                focusedElement: null,
                dropped: { sibling: null, position: null },
                text: this.data.text
            };
        },

        computed: {

            field() {
                return this.$els.input
            },

            sourceField() {
                return this.$els.source;
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
                this.$dispatch('changesMade', true);
            },

            'data.text': function (text, oldText) {
                // Prevent an update when typing directly in the field.
                if (text === this.text) return;

                this.text = text;
                this.$nextTick(() => this.updateEditorHtml(text));
            }

        },

        ready() {
            autosize(this.sourceField);

            this.initMedium();
        },

        methods: {

            /**
             * Used by the InsertsAssets mixin to get the config.
             */
            getFieldtypeConfig() {
                return this.$parent.config;
            },

            plainText() {
                return this.editor.elements[0].textContent;
            },

            addDropAreas() {
                const editor = this.editor.elements[0];

                let firstAdded = false;

                Array.from(editor.children).forEach(child => {
                    if (child.classList.contains('bard-drop-area')) return;

                    if (! firstAdded) {
                        this.addDropAreaBefore(child, editor);
                        firstAdded = true;
                    }

                    this.addDropAreaAfter(child, editor);
                });
            },

            addDropAreaBefore(child, editor) {
                let newNode = document.createElement('div');
                let childNode = document.createElement('div');
                childNode.className += 'bard-drop-area-inner';
                childNode.addEventListener('mouseover', () => {
                    this.dropped.sibling = child;
                    this.dropped.position = 'previous';
                });
                newNode.appendChild(childNode);
                newNode.className += 'bard-drop-area bard-drop-area-before';
                editor.insertBefore(newNode, child);
            },

            addDropAreaAfter(child, editor) {
                let newNode = document.createElement('div');
                let childNode = document.createElement('div');
                childNode.className += 'bard-drop-area-inner';
                childNode.addEventListener('mouseover', () => {
                    this.dropped.sibling = child;
                    this.dropped.position = 'next'
                });
                newNode.appendChild(childNode);
                newNode.className += 'bard-drop-area bard-drop-area-after';
                editor.insertBefore(newNode, child.nextSibling);
            },

            removeDropAreas() {
                const els = this.editor.elements[0].getElementsByClassName('bard-drop-area');
                Array.from(els).forEach(el => el.remove());
                this.text = this.editor.getContent();
            },

            initMedium() {
                let buttons = this.$parent.config.buttons || ['bold', 'italic', 'anchor', 'h2', 'h3', 'quote'];

                let extensions = Object.assign({
                    imageDragging: {},
                    autolist: new AutoList
                }, _.map(Statamic.MediumEditorExtensions, ext => new ext));

                if (this.$parent.config.container) {
                    extensions.assets = this.assetButtonExtension();
                    if (! buttons.includes('assets')) buttons.push('assets');
                }

                let opts = {
                    toolbar: { buttons },
                    autoLink: true,
                    placeholder: false,
                    extensions
                };

                if (this.$parent.config.markdown) {
                    opts.toolbar = false;
                    opts.keyboardCommands = { commands: [
                        { command: false, key: 'B', meta: true, shift: false },
                        { command: false, key: 'I', meta: true, shift: false },
                        { command: false, key: 'U', meta: true, shift: false }
                    ]};
                }

                this.editor = new MediumEditor(this.field, opts);

                this.updateEditorHtml(this.text);

                this.editor.subscribe('editableInput', e => {
                    if (this.editor.getFocusedElement()) {
                        this.focusElement(this.editor.getSelectedParentElement());
                    }

                    // Clean up any annoying span tags that were added by contenteditable.
                    $(this.field).find('span[style]').contents().unwrap();

                    this.text = this.editor.getContent();
                });

                this.editor.subscribe('editableClick', e => {
                    this.focusElement(e.target);
                });

                this.editor.subscribe('editableKeyup', e => {
                    this.focusElement(this.editor.getSelectedParentElement());
                });

                this.editor.subscribe('editableKeydownDelete', e => {
                    const pos = this.editor.exportSelection();

                    if (e.key === 'Backspace') {
                        if (pos.start === 0 && pos.end === 0) this.$emit('backspaced-at-start', this.index);
                    } else if (e.key === 'Delete') {
                        if (pos.start === this.plainText().length && pos.end === this.plainText().length) this.$emit('deleted-at-end', this.index);
                    }
                });

                this.editor.subscribe('editableKeydown', e => {
                    const isUp = e.key === 'ArrowUp' || e.key === 'ArrowLeft';
                    const isDown = e.key === 'ArrowDown' || e.key === 'ArrowRight';

                    if (!isUp && !isDown) return;

                    const pos = this.editor.exportSelection();

                    if (isUp && pos.start === 0 && pos.end === 0) {
                        this.$emit('arrow-up-at-start', this.index);
                    } else if (isDown && pos.start === this.plainText().length && pos.end === this.plainText().length) {
                        this.$emit('arrow-down-at-end', this.index);
                    }
                });
            },

            assetButtonExtension() {
                const vm = this;
                const extension = MediumEditor.extensions.button.extend({
                    name: 'assets',
                    tagNames: ['a'],
                    contentDefault: '<span class="icon icon-images"></span>',
                    aria: 'Assets',
                    handleClick: function () {
                        let toolbar = this.base.getExtensionByName('toolbar');
                        if (toolbar) toolbar.hideToolbar();
                        this.base.saveSelection();
                        vm.addAsset();
                    }
                });
                return new extension;
            },

            moveOptionsToElement(el) {
                this.isShowingOptions = true;
                this.optionsTopPosition = el.offsetTop - 2;
            },

            hideOptions() {
                this.isShowingOptions = false;
            },

            elementIsEmpty(el) {
                const html = el.innerHTML;
                return html === '' || html === '<br>';
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
                this.focusAt('start');
            },

            focusAt(position) {
                if (position === 'start') {
                    position = 0;
                } else if (position === 'end') {
                    position = this.plainText().length;
                }

                this.setCaret(position);
            },

            setCaret(position) {
                this.editor.importSelection({ start: position, end: position });
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

                // Set the caret to the new paragraph position. We'll find the position by inserting
                // a string that shouldn't already exist and we can easily grab from the text.
                // We'll also get rid of the placeholder string after we're done.
                const caretPlaceholder = '%%%CARET%%%';
                newNode.innerHTML = caretPlaceholder;
                this.setCaret(this.plainText().indexOf(caretPlaceholder));
                newNode.innerHTML = '<br>';

                this.text = this.editor.getContent();

                // Bring the + options to the new paragraph.
                this.focusElement(newNode);
            },

            updateEditorHtml() {
                this.editor.setContent(this.text);
            },

            assetsSelected(assets) {
                this.editor.restoreSelection();

                // Loop over returned assets, even though there will only be one.
                this.$http.post(cp_url('assets/get'), { assets }, (response) => {
                    _(response).each(asset => this.editor.createLink({ value: asset.url }));
                });
            }

        }

    }

</script>
