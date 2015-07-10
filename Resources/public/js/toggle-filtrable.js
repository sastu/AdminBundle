jQuery(document).ready(function() {
    $(document).on('click', 'a.toggle-filtrable', function(e) {
        e.preventDefault();

        var button = $(this);

        $(button).addClass('disabled');

        $.post($(this).attr('href'))
            .done(function(is_filtrable) {
                var span = $(button).prev();

                if (1 == is_filtrable) {
                    $(span)
                        .removeClass('label-important')
                        .addClass('label-success')
                        .text($(span).attr('data-yes'));
                } else {
                    $(span)
                        .removeClass('label-success')
                        .addClass('label-important')
                        .text($(span).attr('data-no'));
                }

                $(button).removeClass('disabled');
            });
    });
});