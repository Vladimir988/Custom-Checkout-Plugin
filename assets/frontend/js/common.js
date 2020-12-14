(function ($) {
    $(document).ready(function () {

        if (typeof(passportAttributes) != "undefined" && passportAttributes !== null) {
            let passport = passportAttributes;
            let form = $('form.cart');
            let fields = [
                'passport-edit',
                'upload-passport-image',
                'color-picker',
                'group-modality',
                'first-name',
                'second-name',
                'season',
                'nationality'
            ];
            if (form.length) {
                for (let [key, val] of Object.entries(passport.data)) {
                    if ($.inArray(key, fields) !== -1) {

                        if (key == 'group-modality') {
                            let inputs = form.find(`#${key} input`);
                            inputs.removeAttr('checked');
                            for (i = 0; i < inputs.length; i++) {
                                let input = $(inputs[i]);
                                if (input.val() == val) {
                                    input.prop('checked', true);
                                }
                            }
                        } else {
                            form.find(`input[name="${key}"]`).val(val);
                        }

                        if (key == 'upload-passport-image') {
                            form.find(`#upload_passport_image`).attr('href', val);
                            form.find(`#upload_passport_image img`).attr('src', val);
                        }
                    }
                }
            }
        }

        $('#display_passport_product').on('click', function( event ) {
            event.preventDefault();
            let $this    = $(this);
            let id       = $this.data('product-id');
            let quantity = $this.prev('.quantity_of_passport_products').val();

            window.open(
                $this.data('href') + '?id=' + id + '&quantity=' + quantity,
                '_blank'
            );
        });


        $('#async-upload').on('change', prepareUpload);

        function prepareUpload(event) {

            let canvas = document.getElementById('test-area');
            $('.canvas-wrapp').css({'display': 'block'});
            cropper.start(canvas, 1);

            let file = document.getElementById("async-upload").files[0];

            let reader = new FileReader();
            reader.onload = function (event) {
                let data = event.target.result;

                let image = new Image();
                image.src = event.target.result;

                image.onload = function () {
                    let width  = this.width;
                    let height = this.height;

                    if( Math.max(this.width, this.height) > 500 || Math.min(this.width, this.height) > 500 ) {
                        let coefficient = Math.max(this.width, this.height) / 500;
                        width  = width / coefficient;
                        height = height / coefficient;
                    }

                    $('#test-area').prop('width', width);
                    $('#test-area').prop('height', height);
                };

                cropper.showImage(data);
                cropper.startCropping();
            };

            reader.readAsDataURL(file);
        }

        $('#get-cropped-image-src').on('click', getCroppedImageSrc);

        function getCroppedImageSrc(event) {
            closeCanvas();

            let image = cropper.getCroppedImageSrc();
            let data  = new FormData();
            data.append('action', 'upload_passport_image');
            data.append('image', image);

            $.ajax({
                url : wpData.ajaxUrl,
                type : 'POST',
                data : data,
                cache : false,
                dataType : 'json',
                processData : false,
                contentType : false,
                success : function(data, textStatus, jqXHR) {

                    let link = $('#upload_passport_image');
                    link.attr('href', data.url);
                    link.find('img').attr('src', data.url);
                    $('#upload_passport_image_input').attr('value', data.url);

                }
            });
        }

        $('.close').on('click', closeCanvas);

        function closeCanvas(event) {
            $('.canvas-wrapp').css({'display': 'none'});
        }

    });
})(jQuery);