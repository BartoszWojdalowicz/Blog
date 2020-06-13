$(document).ready(function() {
    console.log("wczytany");
    var $locationSelect = $('div.js-article');
    var $specificLocationTarget = $('.js-specific-tag');

    $locationSelect.on('change', function(e) {
        $.ajax({
            url: $locationSelect.data('specific-tag-url'),
            data: {
                tags: $("input[type='checkbox']:checked").val()
            },

            success: function (html) {
                console.log(html);
                console.log($("input[type='checkbox']:checked").val());
                if ($('.js-specific-tag').html(html)==null) {
                    $specificLocationTarget.find('select').remove();
                    $specificLocationTarget.addClass('d-none');
                    return;
                }
                // Replace the current field and show
                 $specificLocationTarget
                     $('.js-specific-tag').html(html)
                     .removeClass('d-none')
            }
        });
    });
});