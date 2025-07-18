// codemirror-init.js
import CodeMirror from 'codemirror/lib/codemirror';

// Make CodeMirror available globally if needed
if (typeof window !== 'undefined') {
    window.CodeMirror = CodeMirror;
}

// Addons
import 'codemirror/addon/mode/overlay';
import 'codemirror/addon/edit/continuelist';
import 'codemirror/addon/display/placeholder';
import 'codemirror/addon/selection/mark-selection';
import 'codemirror/addon/search/searchcursor';

// Modes
import 'codemirror/mode/clike/clike';
import 'codemirror/mode/cmake/cmake';
import 'codemirror/mode/css/css';
import 'codemirror/mode/diff/diff';
import 'codemirror/mode/django/django';
import 'codemirror/mode/dockerfile/dockerfile';
import 'codemirror/mode/gfm/gfm';
import 'codemirror/mode/go/go';
import 'codemirror/mode/htmlmixed/htmlmixed';
import 'codemirror/mode/http/http';
import 'codemirror/mode/javascript/javascript';
import 'codemirror/mode/jinja2/jinja2';
import 'codemirror/mode/jsx/jsx';
import 'codemirror/mode/markdown/markdown';
import 'codemirror/mode/nginx/nginx';
import 'codemirror/mode/pascal/pascal';
import 'codemirror/mode/perl/perl';
import 'codemirror/mode/php/php';
import 'codemirror/mode/protobuf/protobuf';
import 'codemirror/mode/python/python';
import 'codemirror/mode/ruby/ruby';
import 'codemirror/mode/rust/rust';
import 'codemirror/mode/sass/sass';
import 'codemirror/mode/shell/shell';
import 'codemirror/mode/sql/sql';
import 'codemirror/mode/stylus/stylus';
import 'codemirror/mode/swift/swift';
import 'codemirror/mode/vue/vue';
import 'codemirror/mode/xml/xml';
import 'codemirror/mode/yaml/yaml';

import './markdown-editor/EasyMDE.js';

CodeMirror.commands.tabAndIndentMarkdownList = function (codemirror) {
    var ranges = codemirror.listSelections();
    var pos = ranges[0].head;
    var eolState = codemirror.getStateAfter(pos.line);
    var inList = eolState.list !== false;

    if (inList) {
        codemirror.execCommand('indentMore');
        return;
    }

    if (codemirror.options.indentWithTabs) {
        codemirror.execCommand('insertTab');

        return;
    }

    var spaces = Array(codemirror.options.tabSize + 1).join(' ');
    codemirror.replaceSelection(spaces);
};

CodeMirror.commands.shiftTabAndUnindentMarkdownList = function (codemirror) {
    var ranges = codemirror.listSelections();
    var pos = ranges[0].head;
    var eolState = codemirror.getStateAfter(pos.line);
    var inList = eolState.list !== false;

    if (inList) {
        codemirror.execCommand('indentLess');

        return;
    }

    if (codemirror.options.indentWithTabs) {
        codemirror.execCommand('insertTab');

        return;
    }

    var spaces = Array(codemirror.options.tabSize + 1).join(' ');
    codemirror.replaceSelection(spaces);
};

export default function markdownEditorFormComponent({
    canAttachFiles,
    isLiveDebounced,
    isLiveOnBlur,
    liveDebounce,
    maxHeight,
    minHeight,
    placeholder,
    setUpUsing,
    state,
    translations,
    toolbarButtons,
    uploadFileAttachmentUsing,
}) {
    return {
        editor: null,

        state,

        init: async function () {
            if (this.$root._editor) {
                this.$root._editor.toTextArea();
                this.$root._editor = null;
            }

            this.$root._editor = this.editor = new EasyMDE({
                autoDownloadFontAwesome: false,
                autoRefresh: true,
                autoSave: false,
                element: this.$refs.editor,
                imageAccept: 'image/png, image/jpeg, image/gif, image/avif',
                imageUploadFunction: uploadFileAttachmentUsing,
                initialValue: this.state ?? '',
                maxHeight,
                minHeight,
                placeholder,
                previewImagesInEditor: true,
                spellChecker: false,
                status: [
                    {
                        className: 'upload-image',
                        defaultValue: '',
                    },
                ],
                toolbar: this.getToolbar(),
                uploadImage: canAttachFiles,
            });

            this.editor.codemirror.setOption('direction', document.documentElement?.dir ?? 'ltr');

            // When creating a link, highlight the URL instead of the label:
            this.editor.codemirror.on('changes', (instance, changes) => {
                try {
                    const lastChange = changes[changes.length - 1];

                    if (lastChange.origin === '+input') {
                        const urlPlaceholder = '(https://)';
                        const urlLineText = lastChange.text[lastChange.text.length - 1];

                        if (urlLineText.endsWith(urlPlaceholder) && urlLineText !== '[]' + urlPlaceholder) {
                            const from = lastChange.from;
                            const to = lastChange.to;
                            const isSelectionMultiline = lastChange.text.length > 1;
                            const baseIndex = isSelectionMultiline ? 0 : from.ch;

                            setTimeout(() => {
                                instance.setSelection(
                                    {
                                        line: to.line,
                                        ch: baseIndex + urlLineText.lastIndexOf('(') + 1,
                                    },
                                    {
                                        line: to.line,
                                        ch: baseIndex + urlLineText.lastIndexOf(')'),
                                    },
                                );
                            }, 25);
                        }
                    }
                } catch (error) {
                    // Revert to original behavior.
                }
            });

            this.editor.codemirror.on(
                'change',
                Alpine.debounce(() => {
                    if (!this.editor) {
                        return;
                    }

                    this.state = this.editor.value();

                    if (isLiveDebounced) {
                        this.$wire.call('$refresh');
                    }
                }, liveDebounce ?? 300),
            );

            if (isLiveOnBlur) {
                this.editor.codemirror.on('blur', () => this.$wire.call('$refresh'));
            }

            this.$watch('state', () => {
                if (!this.editor) {
                    return;
                }

                if (this.editor.codemirror.hasFocus()) {
                    return;
                }

                Alpine.raw(this.editor).value(this.state ?? '');
            });

            if (setUpUsing) {
                setUpUsing(this);
            }
        },

        destroy: function () {
            this.editor.cleanup();
            this.editor = null;
        },

        getToolbar: function () {
            let toolbar = [];

            if (toolbarButtons.includes('bold')) {
                toolbar.push({
                    name: 'bold',
                    action: EasyMDE.toggleBold,
                    title: translations.toolbar_buttons?.bold,
                });
            }

            if (toolbarButtons.includes('italic')) {
                toolbar.push({
                    name: 'italic',
                    action: EasyMDE.toggleItalic,
                    title: translations.toolbar_buttons?.italic,
                });
            }

            if (toolbarButtons.includes('strike')) {
                toolbar.push({
                    name: 'strikethrough',
                    action: EasyMDE.toggleStrikethrough,
                    title: translations.toolbar_buttons?.strike,
                });
            }

            if (toolbarButtons.includes('link')) {
                toolbar.push({
                    name: 'link',
                    action: EasyMDE.drawLink,
                    title: translations.toolbar_buttons?.link,
                });
            }

            if (
                ['bold', 'italic', 'strike', 'link'].some((button) => toolbarButtons.includes(button)) &&
                ['heading'].some((button) => toolbarButtons.includes(button))
            ) {
                toolbar.push('|');
            }

            if (toolbarButtons.includes('heading')) {
                toolbar.push({
                    name: 'heading',
                    action: EasyMDE.toggleHeadingSmaller,
                    title: translations.toolbar_buttons?.heading,
                });
            }

            if (
                ['heading'].some((button) => toolbarButtons.includes(button)) &&
                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'].some((button) =>
                    toolbarButtons.includes(button),
                )
            ) {
                toolbar.push('|');
            }

            if (toolbarButtons.includes('blockquote')) {
                toolbar.push({
                    name: 'quote',
                    action: EasyMDE.toggleBlockquote,
                    title: translations.toolbar_buttons?.blockquote,
                });
            }

            if (toolbarButtons.includes('codeBlock')) {
                toolbar.push({
                    name: 'code',
                    action: EasyMDE.toggleCodeBlock,
                    title: translations.toolbar_buttons?.code_block,
                });
            }

            if (toolbarButtons.includes('bulletList')) {
                toolbar.push({
                    name: 'unordered-list',
                    action: EasyMDE.toggleUnorderedList,
                    title: translations.toolbar_buttons?.bullet_list,
                });
            }

            if (toolbarButtons.includes('orderedList')) {
                toolbar.push({
                    name: 'ordered-list',
                    action: EasyMDE.toggleOrderedList,
                    title: translations.toolbar_buttons?.ordered_list,
                });
            }

            if (
                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'].some((button) =>
                    toolbarButtons.includes(button),
                ) &&
                ['table', 'attachFiles'].some((button) => toolbarButtons.includes(button))
            ) {
                toolbar.push('|');
            }

            if (toolbarButtons.includes('table')) {
                toolbar.push({
                    name: 'table',
                    action: EasyMDE.drawTable,
                    title: translations.toolbar_buttons?.table,
                });
            }

            if (toolbarButtons.includes('attachFiles')) {
                toolbar.push({
                    name: 'upload-image',
                    action: EasyMDE.drawUploadedImage,
                    title: translations.toolbar_buttons?.attach_files,
                });
            }

            if (
                ['table', 'attachFiles'].some((button) => toolbarButtons.includes(button)) &&
                ['undo', 'redo'].some((button) => toolbarButtons.includes(button))
            ) {
                toolbar.push('|');
            }

            if (toolbarButtons.includes('undo')) {
                toolbar.push({
                    name: 'undo',
                    action: EasyMDE.undo,
                    title: translations.toolbar_buttons?.undo,
                });
            }

            if (toolbarButtons.includes('redo')) {
                toolbar.push({
                    name: 'redo',
                    action: EasyMDE.redo,
                    title: translations.toolbar_buttons?.redo,
                });
            }

            return toolbar;
        },
    };
}
