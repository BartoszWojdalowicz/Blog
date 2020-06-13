$(document).ready(function() {
    $('.article-like').on('click', function(e) {
        e.preventDefault();
        var $link = $(e.currentTarget);
        $link.toggleClass('fa-heart').toggleClass('fa-heart');
        $.ajax({
            method: 'POST',
            url: $link.attr('href')
        }).done(function(data) {
            $('.article-likes-count').html(data.likes);
        })
    });
});