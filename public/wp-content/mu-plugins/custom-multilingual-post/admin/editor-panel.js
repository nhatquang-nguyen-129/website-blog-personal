(function (wp) {
    var el = wp.element.createElement;
    var useState = wp.element.useState;
    var useEffect = wp.element.useEffect;
    var __ = wp.i18n.__;
    var registerPlugin = wp.plugins.registerPlugin;
    var PluginDocumentSettingPanel =
        (wp.editor && wp.editor.PluginDocumentSettingPanel) ||
        (wp.editPost && wp.editPost.PluginDocumentSettingPanel);
    var useSelect = wp.data.useSelect;
    var useEntityProp = wp.coreData.useEntityProp;
    var apiFetch = wp.apiFetch;
    var SelectControl = wp.components.SelectControl;
    var Button = wp.components.Button;
    var Notice = wp.components.Notice;
    var Spinner = wp.components.Spinner;

    var AVAILABLE_LANGS = (window.mlpEditorPanel && window.mlpEditorPanel.availableLangs) || {};

    function langOptions(codes) {
        return codes.map(function (code) {
            return { label: AVAILABLE_LANGS[code] || code, value: code };
        });
    }

    function MultilingualPanel() {
        var postType = useSelect(function (select) {
            return select('core/editor').getCurrentPostType();
        }, []);
        var postId = useSelect(function (select) {
            return select('core/editor').getCurrentPostId();
        }, []);

        var entityProp = useEntityProp('postType', postType, 'meta');
        var meta = entityProp[0] || {};
        var setMeta = entityProp[1];
        var currentLang = meta._ml_lang || 'vi';

        var itemsState = useState(null);
        var items = itemsState[0];
        var setItems = itemsState[1];

        var loadingState = useState(true);
        var loading = loadingState[0];
        var setLoading = loadingState[1];

        var errorState = useState('');
        var error = errorState[0];
        var setError = errorState[1];

        var newLangState = useState('');
        var newLang = newLangState[0];
        var setNewLang = newLangState[1];

        var addingState = useState(false);
        var adding = addingState[0];
        var setAdding = addingState[1];

        useEffect(
            function () {
                if (!postId) {
                    return;
                }
                setLoading(true);
                apiFetch({ path: '/mlp/v1/groups/' + postId })
                    .then(function (res) {
                        setItems(res.items);
                        setLoading(false);
                    })
                    .catch(function () {
                        setLoading(false);
                        setError(__('Could not load translations.', 'multilingual-post'));
                    });
            },
            [postId]
        );

        var existingLangs = (items || []).map(function (item) {
            return item.lang;
        });
        var remaining = Object.keys(AVAILABLE_LANGS).filter(function (code) {
            return existingLangs.indexOf(code) === -1;
        });

        function handleAdd() {
            if (!newLang) {
                return;
            }
            setAdding(true);
            setError('');
            apiFetch({
                path: '/mlp/v1/translations',
                method: 'POST',
                data: { post_id: postId, lang: newLang },
            })
                .then(function (res) {
                    window.location.href = res.editUrl;
                })
                .catch(function (err) {
                    setAdding(false);
                    setError((err && err.message) || __('Could not create the translation.', 'multilingual-post'));
                });
        }

        return el(
            PluginDocumentSettingPanel,
            { name: 'mlp-multilingual', title: __('Multilingual', 'multilingual-post'), className: 'mlp-panel' },

            el(SelectControl, {
                label: __('Language', 'multilingual-post'),
                value: currentLang,
                options: langOptions(Object.keys(AVAILABLE_LANGS)),
                onChange: function (value) {
                    setMeta(Object.assign({}, meta, { _ml_lang: value }));
                },
                __nextHasNoMarginBottom: true,
            }),

            loading
                ? el(Spinner, {})
                : el(
                      'ul',
                      { className: 'mlp-panel__list' },
                      (items || []).map(function (item) {
                          return el(
                              'li',
                              {
                                  key: item.id,
                                  className: item.isCurrent ? 'mlp-panel__item is-current' : 'mlp-panel__item',
                              },
                              item.isCurrent ? el('span', {}, item.label) : el('a', { href: item.editUrl }, item.label),
                              el('span', { className: 'mlp-panel__status' }, ' — ' + item.status)
                          );
                      })
                  ),

            error
                ? el(
                      Notice,
                      { status: 'error', isDismissible: true, onRemove: function () { setError(''); } },
                      error
                  )
                : null,

            remaining.length > 0
                ? el(
                      'div',
                      { className: 'mlp-panel__add' },
                      el(SelectControl, {
                          label: __('Add translation', 'multilingual-post'),
                          value: newLang,
                          options: [{ label: __('Select a language…', 'multilingual-post'), value: '' }].concat(
                              langOptions(remaining)
                          ),
                          onChange: setNewLang,
                          __nextHasNoMarginBottom: true,
                      }),
                      el(
                          Button,
                          {
                              variant: 'secondary',
                              isBusy: adding,
                              disabled: !newLang || adding,
                              onClick: handleAdd,
                          },
                          __('Add', 'multilingual-post')
                      )
                  )
                : el('p', { className: 'mlp-panel__done' }, __('All supported languages already have a version.', 'multilingual-post'))
        );
    }

    if (PluginDocumentSettingPanel) {
        registerPlugin('mlp-multilingual-panel', {
            render: MultilingualPanel,
            icon: 'translation',
        });
    }
})(window.wp);
