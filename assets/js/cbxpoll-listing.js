'use strict';

function cbxpoll_copyStringToClipboard (str) {
    // Create new element
    var el = document.createElement('textarea');
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


jQuery(document).ready(function ($) {

    var cbxpoll_awn_options = {
        labels: {
            tip: cbxpoll_listing.awn_options.tip,
            info: cbxpoll_listing.awn_options.info,
            success: cbxpoll_listing.awn_options.success,
            warning: cbxpoll_listing.awn_options.warning,
            alert: cbxpoll_listing.awn_options.alert,
            async: cbxpoll_listing.awn_options.async,
            confirm: cbxpoll_listing.awn_options.confirm,
            confirmOk: cbxpoll_listing.awn_options.confirmOk,
            confirmCancel: cbxpoll_listing.awn_options.confirmCancel
        }
    };
    
    //click to copy shortcode
    /*$('.cbxpoll_ctp').on('click', function (e) {
        e.preventDefault();

        var $this = $(this);
        cbxpoll_copyStringToClipboard($this.prev('.cbxpollshortcode').text());

        $this.attr('aria-label', cbxpoll_listing.copied);

        window.setTimeout(function () {
            $this.attr('aria-label', cbxpoll_listing.copy);
        }, 1000);

    });*/


    //click to copy shortcode
    $('.cbxballon_ctp').on('click', function (e) {
        e.preventDefault();

        var $this = $(this);
        cbxpoll_copyStringToClipboard($this.prev('.cbxshortcode').text());

        $this.attr('aria-label', cbxpoll_listing.copycmds.copied_tip);

        window.setTimeout(function () {
            $this.attr('aria-label', cbxpoll_listing.copycmds.copy_tip);
        }, 1000);
    });

    $('.wrap').addClass('cbx-chota cbxpoll-page-wrapper');
    $('#search-submit').addClass('button primary');
    $('#post-query-submit').addClass('button primary');
    //$('.button.action').addClass('button outline primary');
    $('.button.action').addClass('button primary');
    $('.page-title-action').addClass('button primary');
    $('#save-post').addClass('button primary');
    //$('#doaction').addClass('button primary');
    $('#publish').addClass('button primary');

    //$(cbxpoll_admin_js_vars.global_setting_link_html).insertAfter('.page-title-action');
    $('#screen-meta').addClass('cbx-chota cbxpoll-page-wrapper cbxpoll-logs-wrapper');
    $('#screen-options-apply').addClass('primary');


    $('#post-search-input').attr('placeholder', cbxpoll_listing.placeholder.search);
    $('#delete_all').addClass('error');
});