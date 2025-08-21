'use strict';


function cbxpoll_copyStringToClipboard(str) {
    // Create new element
    var el   = document.createElement('textarea');
    // Set value (string to be copied)
    el.value = str;
    // Set non-editable to avoid focus and move outside of view
    el.setAttribute('readonly', '');
    el.style = {position: 'absolute', left: '-9999px'};
    document.body.appendChild(el);
    // Select text inside element
    el.select();
    // Copy text to clipboard
    document.execCommand('copy');
    // Remove temporary element
    document.body.removeChild(el);
}

function cbxpoll_isEmptyOrUndefined(val) {
    return val === undefined || val === null || val === '';
}

function cbxpoll_renderColorPickr($) {
    $('.meta-color-picker-wrapper[data-rendered="0"]').each(function (index, element) {
        var $color_field_wrap = $(element);
        var $color_field      = $color_field_wrap.find('.setting-color-picker');
        var $color_field_fire = $color_field_wrap.find('.setting-color-picker-fire');

        var $current_color = $color_field_fire.data('current-color');
        //var $default_color = $color_field_fire.data('default-color');

        $color_field_wrap.attr('data-rendered', 1);

        // Simple example, see optional options for more configuration.
        var pickr = Pickr.create({
            el: $color_field_fire[0],
            theme: 'classic', // or 'monolith', or 'nano'
            default: $current_color,

            swatches: [
                'rgba(244, 67, 54, 1)',
                'rgba(233, 30, 99, 0.95)',
                'rgba(156, 39, 176, 0.9)',
                'rgba(103, 58, 183, 0.85)',
                'rgba(63, 81, 181, 0.8)',
                'rgba(33, 150, 243, 0.75)',
                'rgba(3, 169, 244, 0.7)',
                'rgba(0, 188, 212, 0.7)',
                'rgba(0, 150, 136, 0.75)',
                'rgba(76, 175, 80, 0.8)',
                'rgba(139, 195, 74, 0.85)',
                'rgba(205, 220, 57, 0.9)',
                'rgba(255, 235, 59, 0.95)',
                'rgba(255, 193, 7, 1)'
            ],

            components: {

                // Main components
                preview: true,
                opacity: true,
                hue: true,

                // Input / output Options
                interaction: {
                    hex: true,
                    rgba: false,
                    hsla: false,
                    hsva: false,
                    cmyk: false,
                    input: true,
                    clear: true,
                    save: true
                }
            },
            i18n: cbxpoll_edit.pickr_i18n
        });

        pickr.on('init', instance => {
            //console.log('Event: "init"', instance);
        }).on('hide', instance => {
            //console.log('Event: "hide"', instance);
        }).on('show', (color, instance) => {
            //console.log('Event: "show"', color, instance);
        }).on('save', (color, instance) => {
            //console.log(color.toHEXA().toString());
            //console.log(color);

            if (color !== null) {
                $color_field_fire.data('current-color', color.toHEXA().toString());
                $color_field.val(color.toHEXA().toString());
            } else {
                $color_field_fire.data('current-color', '');
                $color_field.val('');
            }


            //console.log(instance);
            //console.log(color.toHEXA());
            //console.log(color.toHEX);
        }).on('clear', instance => {
            //console.log('Event: "clear"', instance);
        }).on('change', (color, source, instance) => {
            //console.log('Event: "change"', color, source, instance);

        }).on('changestop', (source, instance) => {
            //console.log('Event: "changestop"', source, instance);
        }).on('cancel', instance => {
            //console.log('Event: "cancel"', instance);
        }).on('swatchselect', (color, instance) => {
            //console.log('Event: "swatchselect"', color, instance);
        });

    });
}

jQuery(document).ready(function ($) {
    var cbxpoll_awn_options = {
        labels: {
            tip: cbxpoll_edit.awn_options.tip,
            info: cbxpoll_edit.awn_options.info,
            success: cbxpoll_edit.awn_options.success,
            warning: cbxpoll_edit.awn_options.warning,
            alert: cbxpoll_edit.awn_options.alert,
            async: cbxpoll_edit.awn_options.async,
            confirm: cbxpoll_edit.awn_options.confirm,
            confirmOk: cbxpoll_edit.awn_options.confirmOk,
            confirmCancel: cbxpoll_edit.awn_options.confirmCancel
        }
    };


    $('.selecttwo-select-wrapper').each(function (index, element) {
        var $element = $(element);

        var $placeholder = $element.data('placeholder');
        var $allow_clear = $element.data('allow-clear');

        if (cbxpoll_isEmptyOrUndefined($placeholder)) $placeholder = cbxpoll_edit.please_select;

        $element
            .find('.selecttwo-select')
            .select2({
                placeholder: $placeholder,
                //searchInputPlaceholder: comfortjob_setting.search,
                allowClear: $allow_clear ? true : false,
                theme: 'default',
                dropdownParent: $element
            })
            .on('select2:open', function () {
                $('.select2-search__field').attr(
                    'placeholder',
                    cbxpoll_edit.placeholder.search
                );
            })
            .on('select2:close', function () {
                $('.select2-search__field').attr('placeholder', $placeholder);
            });

        $element
            .find('.select2-selection__rendered')
            .find('.select2-search--inline .select2-search__field')
            .attr('placeholder', $placeholder);
    });

    $('.cbxpollmetadatepicker').flatpickr({
        disableMobile: 'true',
        // minDate      : new Date(),
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        time_24hr: true,
        defaultHour: 0,
        defaultMinute: 0
    });


    cbxpoll_renderColorPickr($);


    if ($('#cbx_poll_answers_items').length) {
        $('#cbx_poll_answers_items').sortable({
            group: 'no-drop',
            placeholder: 'cbx_poll_items cbx_poll_items_placeholder',
            handle: '.cbx_pollmove',
            onDragStart: function ($item, container, _super) {
                // Duplicate items of the no drop area
                if (!container.options.drop) {
                    $item.clone().insertAfter($item);
                }
                _super($item, container);
            }
        });
    }

    // add new answer
    $('#cbxpoll_answer_wrap').on('click', '.add-cbx-poll-answer', function (event) {
        event.preventDefault();

        var $this            = $(this);
        var $answer_wrap     = $this.closest('#cbxpoll_answer_wrap');
        var $answer_add_wrap = $this.parent('.add-cbx-poll-answer-wrap');

        var $post_id = Number($answer_add_wrap.data('postid'));
        //var $index               = Number($answer_add_wrap.data('answercount'));
        var $index   = Number($('#cbxpoll_answer_extra_answercount').val());
        var $busy    = Number($answer_add_wrap.data('busy'));
        var $type    = $this.data('type');


        //get random answer color
        var answer_color = '#' + '0123456789abcdef'.split('').map(function (v, i, a) {
            return i > 5 ? null : a[Math.floor(Math.random() * 16)];
        }).join('');


        //sending ajax request to get the field template

        if ($busy === 0) {
            $answer_add_wrap.data('busy', 1);

            $.ajax({
                type: 'post',
                dataType: 'json',
                url: cbxpoll_edit.ajaxurl,
                data: {
                    action: 'cbxpoll_get_answer_template',
                    answer_counter: $index,
                    answer_color: answer_color,
                    is_voted: 0,
                    post_id: $post_id,
                    answer_type: $type,
                    security: cbxpoll_edit.nonce
                },
                success: function (data, textStatus, XMLHttpRequest) {
                    $('#cbx_poll_answers_items').append(data);

                    cbxpoll_renderColorPickr($);

                    wp.cbxpolljshooks.doAction('cbxpoll_new_answer_template_render', $index, $type, answer_color, $post_id, $);

                    $index++;
                    //$answer_add_wrap.data('answercount', $index);
                    $('#cbxpoll_answer_extra_answercount').val($index);
                    $answer_add_wrap.data('busy', 0);
                }
            });
        }

    });


    //remove an answer
    $('#cbxpoll_answer_wrap').on('click', '.cbx_pollremove', function (event) {
        event.preventDefault();

        var $this = $(this);

        var notifier = new AWN(cbxpoll_awn_options);

        var onCancel = () => {

        };

        var onOk = () => {
            $this.closest('.cbx_poll_items').remove();
        }

        notifier.confirm(
            cbxpoll_edit.are_you_sure_delete_desc,
            onOk,
            onCancel,
            {
                labels: {
                    confirm: cbxpoll_edit.are_you_sure_global
                }
            }
        );
    });


    //click to copy shortcode
    $('.cbxballon_ctp').on('click', function (e) {
        e.preventDefault();

        var $this = $(this);
        cbxpoll_copyStringToClipboard($this.prev('.cbxshortcode').text());

        $this.attr('aria-label', cbxpoll_edit.copycmds.copied_tip);

        window.setTimeout(function () {
            $this.attr('aria-label', cbxpoll_edit.copycmds.copy_tip);
        }, 1000);
    });

    $('.wrap').addClass('cbx-chota cbxpoll-page-wrapper cbxpoll-addedit-wrapper');
    $('.page-title-action').addClass('button primary');
    $('#save-post').addClass('button primary');
    $('#post-preview').addClass('button outline');
    //$('#doaction').addClass('button primary');
    $('#publish').addClass('button primary');

    $('#screen-meta').addClass('cbx-chota cbxpoll-page-wrapper cbxpoll-logs-wrapper');
    $('#screen-options-apply').addClass('primary');
});
