(function (blocks, element, components, editor, data) {
    var el = element.createElement;
    var RichText = editor.RichText;
    var InspectorControls = editor.InspectorControls;
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var __ = wp.i18n.__;
    var useState = element.useState;
    var useEffect = element.useEffect;

    blocks.registerBlockType('meinturnierplan/table', {
        title: __('Tournament Table', 'meinturnierplan-wp'),
        icon: 'editor-table',
        category: 'widgets',
        attributes: {
            tableId: {
                type: 'string',
                default: ''
            },
            width: {
                type: 'string',
                default: ''
            }
        },

        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var previewHtml = useState('');
            var setPreviewHtml = previewHtml[1];
            var isLoading = useState(false);
            var setIsLoading = isLoading[1];

            // Get tournament tables (this would need to be populated via wp_localize_script)
            var tableOptions = [
                { value: '', label: __('-- Select Table --', 'meinturnierplan-wp') }
            ];

            // Add available tables from localized data
            if (typeof mtpBlockData !== 'undefined' && mtpBlockData.tables) {
                mtpBlockData.tables.forEach(function(table) {
                    tableOptions.push({
                        value: table.id,
                        label: table.title
                    });
                });
            }
            
            // Function to load preview
            var loadPreview = function(tableId, width) {
                if (!tableId) {
                    setPreviewHtml('');
                    setIsLoading(false);
                    return;
                }
                
                setIsLoading(true);
                
                var data = new FormData();
                data.append('action', 'mtp_block_preview');
                data.append('table_id', tableId);
                data.append('width', width || '');
                data.append('nonce', mtpBlockData.nonce);
                
                fetch(mtpBlockData.ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: data
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(result) {
                    setIsLoading(false);
                    if (result.success) {
                        setPreviewHtml(result.data);
                    } else {
                        setPreviewHtml('<p style="color: red;">' + (result.data || 'Error loading preview') + '</p>');
                    }
                })
                .catch(function(error) {
                    setIsLoading(false);
                    setPreviewHtml('<p style="color: red;">Error loading preview</p>');
                });
            };
            
            // Load initial preview and update when attributes change
            useEffect(function() {
                loadPreview(attributes.tableId, attributes.width);
            }, [attributes.tableId, attributes.width]);

            return [
                el(InspectorControls, {
                    key: 'inspector'
                },
                    el(PanelBody, {
                        title: __('Tournament Table Settings', 'meinturnierplan-wp'),
                        initialOpen: true
                    },
                        el(SelectControl, {
                            label: __('Select Tournament Table', 'meinturnierplan-wp'),
                            value: attributes.tableId,
                            options: tableOptions,
                            onChange: function (value) {
                                setAttributes({ tableId: value });
                            }
                        }),
                        el(TextControl, {
                            label: __('Custom Width (px)', 'meinturnierplan-wp'),
                            value: attributes.width,
                            type: 'number',
                            min: 100,
                            max: 2000,
                            onChange: function (value) {
                                setAttributes({ width: value });
                            },
                            help: __('Leave empty to use table default width.', 'meinturnierplan-wp')
                        })
                    )
                ),

                el('div', {
                    key: 'block-content',
                    className: 'mtp-tournament-table-block'
                },
                    attributes.tableId ? 
                        (isLoading[0] ?
                            el('div', {
                                style: {
                                    border: '1px solid #ddd',
                                    padding: '20px',
                                    backgroundColor: '#f9f9f9',
                                    textAlign: 'center',
                                    color: '#666'
                                }
                            },
                                el('p', null, __('Loading preview...', 'meinturnierplan-wp'))
                            ) :
                            (previewHtml[0] ?
                                el('div', {
                                    style: {
                                        border: '1px solid #ddd',
                                        padding: '10px',
                                        backgroundColor: '#f9f9f9'
                                    },
                                    dangerouslySetInnerHTML: { __html: previewHtml[0] }
                                }) :
                                el('div', {
                                    style: {
                                        border: '1px solid #ddd',
                                        padding: '10px',
                                        backgroundColor: '#f9f9f9',
                                        textAlign: 'center'
                                    }
                                },
                                    el('p', null, __('Tournament Table Preview', 'meinturnierplan-wp')),
                                    el('p', null, __('Table ID:', 'meinturnierplan-wp') + ' ' + attributes.tableId),
                                    attributes.width ? el('p', null, __('Width:', 'meinturnierplan-wp') + ' ' + attributes.width + 'px') : null
                                )
                            )
                        ) :
                        el('div', {
                            style: {
                                border: '2px dashed #ccc',
                                padding: '20px',
                                textAlign: 'center',
                                color: '#666'
                            }
                        },
                            el('p', null, __('Please select a tournament table from the sidebar.', 'meinturnierplan-wp'))
                        )
                )
            ];
        },

        save: function (props) {
            // Return null to render via PHP callback
            return null;
        }
    });

})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor || window.wp.editor,
    window.wp.data
);
