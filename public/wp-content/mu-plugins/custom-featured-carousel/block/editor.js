(function (wp) {
    var el = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var useState = wp.element.useState;
    var useEffect = wp.element.useEffect;
    var __ = wp.i18n.__;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var RangeControl = wp.components.RangeControl;
    var TextControl = wp.components.TextControl;
    var Button = wp.components.Button;
    var Notice = wp.components.Notice;
    var Spinner = wp.components.Spinner;
    var apiFetch = wp.apiFetch;

    var MAX_POSTS = 10;

    /**
     * A pasted permalink can be either "plain" (?p=123) or "pretty"
     * (/my-post-slug/) depending on the site's permalink settings, so try
     * both: a numeric ?p= query arg first, then fall back to treating the
     * last path segment as a slug.
     */
    function resolvePostFromUrl(rawUrl) {
        var url;
        try {
            url = new URL(rawUrl, window.location.origin);
        } catch (e) {
            return Promise.resolve(null);
        }

        var pParam = url.searchParams.get('p');
        if (pParam && /^\d+$/.test(pParam)) {
            return apiFetch({ path: '/wp/v2/posts/' + pParam }).catch(function () {
                return null;
            });
        }

        var segments = url.pathname.split('/').filter(Boolean);
        var slug = segments.length ? segments[segments.length - 1] : '';
        if (!slug) {
            return Promise.resolve(null);
        }

        return apiFetch({ path: '/wp/v2/posts?slug=' + encodeURIComponent(slug) })
            .then(function (results) {
                return results && results[0] ? results[0] : null;
            })
            .catch(function () {
                return null;
            });
    }

    wp.blocks.registerBlockType('minimal-reader/featured-carousel', {
        apiVersion: 3,
        title: __('Featured Carousel', 'featured-carousel'),
        description: __('An auto-advancing carousel. Add posts by pasting their link, drag to reorder, set the slide delay.', 'featured-carousel'),
        category: 'design',
        icon: 'images-alt2',
        textdomain: 'featured-carousel',
        supports: {
            html: false,
            align: ['wide', 'full'],
            multiple: false,
        },
        attributes: {
            postIds: { type: 'array', default: [] },
            delay: { type: 'number', default: 5000 },
        },

        edit: function (props) {
            var attributes = props.attributes;
            var postIds = attributes.postIds || [];
            var blockProps = useBlockProps({ className: 'mlfc-carousel-editor' });

            var titlesState = useState({});
            var titles = titlesState[0];
            var setTitles = titlesState[1];

            var urlState = useState('');
            var urlInput = urlState[0];
            var setUrlInput = urlState[1];

            var errorState = useState('');
            var error = errorState[0];
            var setError = errorState[1];

            var resolvingState = useState(false);
            var resolving = resolvingState[0];
            var setResolving = resolvingState[1];

            var dragIndexState = useState(null);
            var dragIndex = dragIndexState[0];
            var setDragIndex = dragIndexState[1];

            // Resolve titles for posts already on the block (e.g. reopening it later).
            useEffect(function () {
                var missing = postIds.filter(function (id) {
                    return !titles[id];
                });
                if (!missing.length) {
                    return;
                }
                apiFetch({ path: '/wp/v2/posts?include=' + missing.join(',') + '&per_page=100&_fields=id,title' }).then(
                    function (posts) {
                        setTitles(function (prev) {
                            var next = Object.assign({}, prev);
                            posts.forEach(function (post) {
                                next[post.id] = post.title.rendered || __('(untitled)', 'featured-carousel');
                            });
                            return next;
                        });
                    }
                );
                // eslint-disable-next-line
            }, [postIds.join(',')]);

            function handleAdd() {
                if (!urlInput || postIds.length >= MAX_POSTS) {
                    return;
                }
                setResolving(true);
                setError('');
                resolvePostFromUrl(urlInput).then(function (post) {
                    setResolving(false);
                    if (!post || !post.id) {
                        setError(__('Could not find a post at that link.', 'featured-carousel'));
                        return;
                    }
                    if (postIds.indexOf(post.id) !== -1) {
                        setError(__('That post is already in the list.', 'featured-carousel'));
                        return;
                    }
                    setTitles(function (prev) {
                        var next = Object.assign({}, prev);
                        next[post.id] = post.title.rendered || __('(untitled)', 'featured-carousel');
                        return next;
                    });
                    props.setAttributes({ postIds: postIds.concat([post.id]) });
                    setUrlInput('');
                });
            }

            function handleRemove(index) {
                var next = postIds.slice();
                next.splice(index, 1);
                props.setAttributes({ postIds: next });
            }

            function handleDrop(targetIndex) {
                if (dragIndex === null || dragIndex === targetIndex) {
                    setDragIndex(null);
                    return;
                }
                var next = postIds.slice();
                var moved = next.splice(dragIndex, 1)[0];
                next.splice(targetIndex, 0, moved);
                props.setAttributes({ postIds: next });
                setDragIndex(null);
            }

            return el(
                Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: __('Featured posts', 'featured-carousel') },
                        el(TextControl, {
                            label: __('Post link', 'featured-carousel'),
                            placeholder: __('Paste a post URL…', 'featured-carousel'),
                            value: urlInput,
                            onChange: setUrlInput,
                            disabled: postIds.length >= MAX_POSTS,
                            __nextHasNoMarginBottom: true,
                        }),
                        el(
                            Button,
                            {
                                variant: 'secondary',
                                isBusy: resolving,
                                disabled: !urlInput || resolving || postIds.length >= MAX_POSTS,
                                onClick: handleAdd,
                                style: { marginTop: '0.5rem', marginBottom: '0.75rem' },
                            },
                            __('Add', 'featured-carousel')
                        ),
                        error
                            ? el(
                                  Notice,
                                  { status: 'error', isDismissible: true, onRemove: function () { setError(''); } },
                                  error
                              )
                            : null,
                        el(
                            'p',
                            { className: 'mlfc-help' },
                            postIds.length >= MAX_POSTS
                                ? __('Maximum of 10 posts reached.', 'featured-carousel')
                                : postIds.length + '/10 ' + __('posts added. Drag to reorder.', 'featured-carousel')
                        ),
                        el(
                            'ul',
                            { className: 'mlfc-post-list' },
                            postIds.map(function (id, index) {
                                return el(
                                    'li',
                                    {
                                        key: id,
                                        className: 'mlfc-post-list__item' + (dragIndex === index ? ' is-dragging' : ''),
                                        draggable: true,
                                        onDragStart: function () {
                                            setDragIndex(index);
                                        },
                                        onDragOver: function (e) {
                                            e.preventDefault();
                                        },
                                        onDrop: function () {
                                            handleDrop(index);
                                        },
                                        onDragEnd: function () {
                                            setDragIndex(null);
                                        },
                                    },
                                    el('span', { className: 'mlfc-post-list__handle' }, '⠿'),
                                    el('span', { className: 'mlfc-post-list__title' }, titles[id] || '#' + id),
                                    el(Button, {
                                        icon: 'no-alt',
                                        label: __('Remove', 'featured-carousel'),
                                        onClick: function () {
                                            handleRemove(index);
                                        },
                                    })
                                );
                            })
                        )
                    ),
                    el(
                        PanelBody,
                        { title: __('Autoplay', 'featured-carousel') },
                        el(RangeControl, {
                            label: __('Slide delay (ms)', 'featured-carousel'),
                            value: attributes.delay,
                            onChange: function (value) {
                                props.setAttributes({ delay: value });
                            },
                            min: 1000,
                            max: 15000,
                            step: 500,
                            __nextHasNoMarginBottom: true,
                        })
                    )
                ),
                el(
                    'div',
                    blockProps,
                    el('strong', {}, '🎠 ' + __('Featured Carousel', 'featured-carousel')),
                    postIds.length === 0
                        ? el(
                              'p',
                              { className: 'mlfc-warning' },
                              __('Add at least one post in the sidebar (“Featured posts”) — nothing shows on the frontend until you do.', 'featured-carousel')
                          )
                        : el(
                              'ul',
                              {},
                              postIds.map(function (id) {
                                  return el('li', { key: id }, titles[id] || '#' + id);
                              })
                          )
                )
            );
        },

        save: function () {
            // Dynamic block: actual markup comes from block.json's "render" (render.php).
            return null;
        },
    });
})(window.wp);
