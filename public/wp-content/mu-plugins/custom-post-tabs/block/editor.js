(function (blocks, element, blockEditor, components, i18n) {
    var el = element.createElement;
    var __ = i18n.__;
    var useBlockProps = blockEditor.useBlockProps;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var RangeControl = components.RangeControl;

    blocks.registerBlockType('minimal-reader/post-tabs', {
        apiVersion: 3,
        title: __('Post Tabs', 'post-tabs'),
        description: __('A tabbed posts list — Latest and Discussions (most-commented) — switchable without a page reload.', 'post-tabs'),
        category: 'design',
        icon: 'screenoptions',
        textdomain: 'post-tabs',
        supports: {
            html: false,
            align: ['wide', 'full'],
            multiple: false,
        },
        attributes: {
            postsPerTab: {
                type: 'number',
                default: 5,
            },
        },
        edit: function (props) {
            var attributes = props.attributes;
            var blockProps = useBlockProps({ className: 'mlpt-post-tabs-editor' });

            return el(
                'div',
                blockProps,
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: __('Post Tabs', 'post-tabs') },
                        el(RangeControl, {
                            label: __('Posts per tab', 'post-tabs'),
                            value: attributes.postsPerTab,
                            onChange: function (value) {
                                props.setAttributes({ postsPerTab: value });
                            },
                            min: 1,
                            max: 10,
                            __nextHasNoMarginBottom: true,
                        })
                    )
                ),
                el('strong', {}, '📑 ' + __('Post Tabs', 'post-tabs')),
                el(
                    'p',
                    {},
                    __('Renders “Latest” and “Discussions” (most-commented) tabs of real posts on the frontend — nothing to preview here.', 'post-tabs')
                )
            );
        },
        save: function () {
            // Dynamic block: actual markup comes from block.json's "render" (render.php).
            return null;
        },
    });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n);
