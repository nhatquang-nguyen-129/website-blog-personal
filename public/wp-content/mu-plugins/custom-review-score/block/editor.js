(function (blocks, element, blockEditor, components, coreData, data, i18n) {
    var el = element.createElement;
    var __ = i18n.__;
    var useBlockProps = blockEditor.useBlockProps;
    var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
    var InnerBlocks = blockEditor.InnerBlocks;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var RangeControl = components.RangeControl;
    var TextControl = components.TextControl;
    var Notice = components.Notice;
    var useEntityProp = coreData.useEntityProp;
    var useSelect = data.useSelect;

    var META_KEY = '_mlrs_score';
    var DEFAULT_LABEL = 'Đánh giá';

    // The label shows above the number in the box and as the tilted tag on
    // the Featured Carousel badge. The tightest of the two is the box's
    // score panel on desktop (14rem wide, label at 15px uppercase), which
    // fits about 16 characters on one line. The carousel tag fits far more.
    var MAX_LABEL_CHARS = 16;

    // Counted in characters as a reader sees them: Vietnamese typed with
    // combining accents ("a" + "́") is normalized first, so "á" counts as 1.
    function chars(text) {
        return Array.from((text || '').normalize('NFC'));
    }

    // Mirrors mlrs_score_tier() in core/score.php, so the canvas preview's
    // color matches what the frontend will render.
    function tierFor(score) {
        if (score >= 9) return 'excellent';
        if (score >= 7) return 'good';
        if (score >= 5) return 'mixed';
        return 'poor';
    }

    function formatScore(score) {
        var fixed = score.toFixed(1);
        return fixed.slice(-2) === '.0' ? fixed.slice(0, -2) : fixed;
    }

    blocks.registerBlockType('minimal-reader/review-score', {
        apiVersion: 3,
        title: __('Review Score', 'review-score'),
        category: 'design',
        icon: 'star-filled',
        textdomain: 'review-score',
        usesContext: ['postId', 'postType'],
        supports: {
            html: false,
            multiple: false,
        },
        attributes: {
            label: { type: 'string', default: 'Đánh giá' },
        },
        edit: function (props) {
            var attributes = props.attributes;

            var editorPost = useSelect(function (select) {
                var editor = select('core/editor');
                return editor
                    ? { id: editor.getCurrentPostId(), type: editor.getCurrentPostType() }
                    : {};
            }, []);
            var postType = props.context.postType || editorPost.type;
            var postId = props.context.postId || editorPost.id;

            // The score is post meta, not a block attribute: post lists and
            // the carousel need to read it without parsing this post's
            // content. Saved together with the post's normal Save/Update.
            var entity = useEntityProp('postType', postType, 'meta', postId);
            var meta = entity[0] || {};
            var setMeta = entity[1];

            var raw = meta[META_KEY];
            var score = raw === '' || raw === undefined || raw === null ? null : parseFloat(raw);
            var hasScore = score !== null && !isNaN(score);

            function setScore(value) {
                var next = Object.assign({}, meta);
                next[META_KEY] = value === undefined || value === null || isNaN(value) ? '' : String(value);
                setMeta(next);
            }

            // Earlier versions edited the label as RichText in the canvas, so
            // a stored value may still carry inline markup.
            var label = (attributes.label || '').replace(/<[^>]*>/g, '');
            var labelLength = chars(label).length;

            var blockProps = useBlockProps({
                className: 'mlrs-box mlrs-box--editor ' + (hasScore ? 'mlrs-tier--' + tierFor(score) : 'mlrs-box--empty'),
            });

            var innerBlocksProps = useInnerBlocksProps(
                { className: 'mlrs-box__summary-body' },
                {
                    // No headings: custom-table-of-contents lists every
                    // h1–h4 in the post, and the summary isn't a section.
                    allowedBlocks: ['core/paragraph', 'core/list'],
                    template: [['core/paragraph', { placeholder: __('Viết phần tổng kết của bài đánh giá…', 'review-score') }]],
                }
            );

            return el(
                element.Fragment,
                null,
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: __('Review score', 'review-score') },
                        el(RangeControl, {
                            label: __('Score (out of 10)', 'review-score'),
                            value: hasScore ? score : undefined,
                            onChange: setScore,
                            min: 0,
                            max: 10,
                            step: 0.1,
                            allowReset: true,
                            initialPosition: 8,
                        }),
                        el(TextControl, {
                            label: __('Label', 'review-score'),
                            value: label,
                            placeholder: DEFAULT_LABEL,
                            onChange: function (value) {
                                // Typing/pasting stops at the limit. An older,
                                // already-longer label isn't silently cut (the
                                // warning below asks to shorten it); it just
                                // can't grow further.
                                var next = chars(value);
                                if (next.length > MAX_LABEL_CHARS && next.length > labelLength) {
                                    next = next.slice(0, Math.max(MAX_LABEL_CHARS, labelLength));
                                }
                                props.setAttributes({ label: next.join('') });
                            },
                        }),
                        labelLength > MAX_LABEL_CHARS && el(
                            Notice,
                            { status: 'warning', isDismissible: false },
                            __('Longer than 16 characters: wraps onto two lines in the score box.', 'review-score')
                        )
                    )
                ),
                el(
                    'section',
                    blockProps,
                    el(
                        'div',
                        { className: 'mlrs-box__score' },
                        el('span', { className: 'mlrs-box__label' }, label || DEFAULT_LABEL),
                        el(
                            'span',
                            { className: 'mlrs-box__value' },
                            el('span', { className: 'mlrs-box__number' }, hasScore ? formatScore(score) : '–'),
                            el('span', { className: 'mlrs-box__max' }, '/10')
                        ),
                        !hasScore && el(
                            'span',
                            { className: 'mlrs-box__hint' },
                            __('Nhập điểm ở thanh bên phải →', 'review-score')
                        )
                    ),
                    el('div', { className: 'mlrs-box__summary' }, el('div', innerBlocksProps))
                )
            );
        },
        save: function () {
            // Dynamic block: the box itself comes from render.php; only the
            // summary's inner blocks are saved, and passed to it as $content.
            return el(InnerBlocks.Content);
        },
    });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.coreData, window.wp.data, window.wp.i18n);
