
jQuery(document).ready(function($) {

    $('.toggle-view li').click(function () {

        var text = $(this).children('div.panel');

        if (text.is(':hidden')) {
            text.slideDown('200');
            $(this).children('span').html('-');
        } else {
            text.slideUp('200');
            $(this).children('span').html('+');
        }

    });
});