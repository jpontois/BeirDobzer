$(document).ready(function() {

    $('body').on('click', '.menu', function(e) {
        e.preventDefault();
        $('#container').load($(this).attr('href'));
    });

    $('#container').on('submit', 'form', function(e) {
        e.preventDefault();
        $(this).toggleClass('active');
        form = $(this);

        $.ajax({
            url : form.attr('action'),
            type : 'POST',
            data : new FormData(this),
            contentType: false,
            processData: false,

            success : function(result) {
                $('#container').html($.parseHTML(result));
            },

            error : function() {
                alert('erreur');
            }
        });
    });

    $(document)
    .ajaxStart(function () {
        $('.loaderCover').css('display', 'block');
    })
    .ajaxStop(function () {
        $('.loaderCover').css('display', 'none');
    });
});