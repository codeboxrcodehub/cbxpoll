'use strict';

(function (blocks, element, components, editor, ServerSideRender, blockEditor) {
    var el                = element.createElement,
        registerBlockType = blocks.registerBlockType,

        //InspectorControls = editor.InspectorControls,
        InspectorControls = blockEditor.InspectorControls,
        //ServerSideRender  = components.ServerSideRender,

        RangeControl      = components.RangeControl,
        Panel             = components.Panel,
        PanelBody         = components.PanelBody,
        PanelRow          = components.PanelRow,
        TextControl       = components.TextControl,
        //NumberControl = components.NumberControl,
        TextareaControl   = components.TextareaControl,
        CheckboxControl   = components.CheckboxControl,
        RadioControl      = components.RadioControl,
        SelectControl     = components.SelectControl,
        ToggleControl     = components.ToggleControl,
        //ColorPicker = components.ColorPalette,
        //ColorPicker = components.ColorPicker,
        //ColorPicker = components.ColorIndicator,
        PanelColorPicker  = editor.PanelColorSettings,
        DateTimePicker    = components.DateTimePicker,
        HorizontalRule    = components.HorizontalRule,
        ExternalLink      = components.ExternalLink;

    //var MediaUpload = wp.editor.MediaUpload;

    //https://www.flaticon.com/free-icon/poll_1246239?term=polling&page=1&position=80
    var iconEl = el('svg', {width: 24, height: 24},
        el('path', {d: 'M 22 0 L 2 0 C 0.894531 0 0 0.894531 0 2 L 0 22 C 0 23.105469 0.894531 24 2 24 L 22 24 C 23.105469 24 24 23.105469 24 22 L 24 2 C 24 0.894531 23.105469 0 22 0 Z M 23.199219 22 C 23.199219 22.664062 22.664062 23.199219 22 23.199219 L 2 23.199219 C 1.335938 23.199219 0.800781 22.664062 0.800781 22 L 0.800781 2 C 0.800781 1.335938 1.335938 0.800781 2 0.800781 L 22 0.800781 C 22.664062 0.800781 23.199219 1.335938 23.199219 2 Z M 23.199219 22 '}),
        el('path', {d: 'M 6.800781 8.800781 L 2.800781 8.800781 C 2.578125 8.800781 2.398438 8.980469 2.398438 9.199219 L 2.398438 21.199219 C 2.398438 21.421875 2.578125 21.601562 2.800781 21.601562 L 6.800781 21.601562 C 7.019531 21.601562 7.199219 21.421875 7.199219 21.199219 L 7.199219 9.199219 C 7.199219 8.980469 7.019531 8.800781 6.800781 8.800781 Z M 6.398438 20.800781 L 3.199219 20.800781 L 3.199219 9.601562 L 6.398438 9.601562 Z M 6.398438 20.800781 '}),
        el('path', {d: 'M 14 2.398438 L 10 2.398438 C 9.777344 2.398438 9.601562 2.578125 9.601562 2.800781 L 9.601562 21.199219 C 9.601562 21.421875 9.777344 21.601562 10 21.601562 L 14 21.601562 C 14.222656 21.601562 14.398438 21.421875 14.398438 21.199219 L 14.398438 2.800781 C 14.398438 2.578125 14.222656 2.398438 14 2.398438 Z M 13.601562 20.800781 L 10.398438 20.800781 L 10.398438 3.199219 L 13.601562 3.199219 Z M 13.601562 20.800781 '}),
        el('path', {d: 'M 21.199219 12.800781 L 17.199219 12.800781 C 16.980469 12.800781 16.800781 12.980469 16.800781 13.199219 L 16.800781 21.199219 C 16.800781 21.421875 16.980469 21.601562 17.199219 21.601562 L 21.199219 21.601562 C 21.421875 21.601562 21.601562 21.421875 21.601562 21.199219 L 21.601562 13.199219 C 21.601562 12.980469 21.421875 12.800781 21.199219 12.800781 Z M 20.800781 20.800781 L 17.601562 20.800781 L 17.601562 13.601562 L 20.800781 13.601562 Z M 20.800781 20.800781 '}),
    );

    registerBlockType('codeboxr/cbxpoll-single', {
        title: cbxpoll_singlepoll_block.block_title,
        icon: iconEl,
        category: cbxpoll_singlepoll_block.block_category,

        /*
         * In most other blocks, you'd see an 'attributes' property being defined here.
         * We've defined attributes in the PHP, that information is automatically sent
         * to the block editor, so we don't need to redefine it here.
         */
        edit: function (props) {


            return [
                /*
                 * The ServerSideRender element uses the REST API to automatically call
                 * php_block_render() in your PHP code whenever it needs to get an updated
                 * view of the block.
                 */
                el(ServerSideRender, {
                    block: 'codeboxr/cbxpoll-single',
                    attributes: props.attributes,
                }),

                el(InspectorControls, {},
                    // 1st Panel – Form Settings
                    el(PanelBody, {title: cbxpoll_singlepoll_block.general_settings.heading, initialOpen: true},
                        el(SelectControl,
                            {
                                label: cbxpoll_singlepoll_block.general_settings.poll_id,
                                options: cbxpoll_singlepoll_block.general_settings.poll_id_options,
                                onChange: (value) => {
                                    props.setAttributes({poll_id: Number(value)});
                                },
                                value: props.attributes.poll_id,
                            }
                        ),
                        el(SelectControl,
                            {
                                label: cbxpoll_singlepoll_block.general_settings.chart_type,
                                options: cbxpoll_singlepoll_block.general_settings.chart_type_options,
                                onChange: (value) => {
                                    props.setAttributes({chart_type: value});
                                },
                                value: props.attributes.chart_type,
                            }
                        ),
                        el(SelectControl,
                            {
                                label: cbxpoll_singlepoll_block.general_settings.description,
                                options: cbxpoll_singlepoll_block.general_settings.description_options,
                                onChange: (value) => {
                                    props.setAttributes({description: Number(value)});
                                },
                                value: props.attributes.description,
                            }
                        ),
                        el(SelectControl,
                            {
                                label: cbxpoll_singlepoll_block.general_settings.grid,
                                options: cbxpoll_singlepoll_block.general_settings.grid_options,
                                onChange: (value) => {
                                    props.setAttributes({grid: Number(value)});
                                },
                                value: props.attributes.grid
                            },
                        ),
                    ),
                ),

            ];
        },
        // We're going to be rendering in PHP, so save() can just return null.
        save: function () {
            return null;
        }
    });
}(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.editor,
    window.wp.serverSideRender,
    window.wp.blockEditor
));