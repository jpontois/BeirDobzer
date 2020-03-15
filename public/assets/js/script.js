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

    function readURL(input) {

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                $('#img_preview').remove();
                $("#img_tps").parent().append('<img id="img_preview" src="#"/>');
                $('#img_preview').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
    
    $('body').on('change', 'input', function() {
        $('#img_tps').remove();
        $(this).parent().append('<img id="img_tps"/>');
        readURL(this);
    });
});