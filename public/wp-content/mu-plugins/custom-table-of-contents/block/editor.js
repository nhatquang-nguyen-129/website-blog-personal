(function (blocks, element, blockEditor, i18n) {
    var el = element.createElement;
    var __ = i18n.__;
    var useBlockProps = blockEditor.useBlockProps;
    var RichText = blockEditor.RichText;

    blocks.registerBlockType('minimal-reader/table-of-contents', {
        apiVersion: 3,
        title: __('Table of Contents', 'table-of-contents'),
        description: __('An auto-generated list of links to this post’s headings. Nothing to configure — just insert it where you want it to appear.', 'table-of-contents'),
        category: 'design',
        icon: 'list-view',
        textdomain: 'table-of-contents',
        supports: {
            html: false,
            multiple: false,
        },
        attributes: {
            title: {
                type: 'string',
                default: 'Mục lục',
            },
        },
        edit: function (props) {
            var attributes = props.attributes;
            var blockProps = useBlockProps({ className: 'mlptoc mlptoc--editor' });

            return el(
                'div',
                blockProps,
                el(RichText, {
                    tagName: 'p',
                    className: 'mlptoc__title',
                    value: attributes.title,
                    onChange: function (title) {
                        props.setAttributes({ title: title });
                    },
                    placeholder: __('Mục lục', 'table-of-contents'),
                }),
                el(
                    'p',
                    { className: 'mlptoc__editor-note' },
                    __('The list itself is built automatically from this post’s headings when the post is viewed — nothing else to edit here.', 'table-of-contents')
                )
            );
        },
        save: function () {
            // Dynamic block: actual markup comes from block.json's "render" (render.php).
            return null;
        },
    });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.i18n);
