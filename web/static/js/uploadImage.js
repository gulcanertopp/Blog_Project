(function( $ ){

    $.fn.uploadableImage = function (params) {

        var field = $(this);

        if(field.attr('id') == 'undefined'){
            console.log('Uploadable Image requires id attribute');
            return;
        }

        var panel = $('<div />').attr('id','widget_'+field.attr('id')).addClass('widget').append([
            $('<div>').addClass('media-drop').append([
                $('<input type="file">').attr('id','media-drop-file').attr('name','file'),
                $('<span />').text(params.description)
            ]),
            $('<div>').addClass('media-preview')
        ]);


        field.hide();
        field.parent().append(panel);

        var preview   = panel.find('.media-preview');
        var mediaDrop = panel.find('.media-drop');
        var file      = panel.find('#media-drop-file');

        fillValue();

        configureDrop();

        $(document).on('click', '#'+panel.attr('id')+' .media-item .remove', function () {
            field.val('');
            preview.empty();
            mediaDrop.show();
        });

        $(document).on('change','#'+field.attr('id'), function () {
            fillValue();
        });

        function fillValue() {
            var url = field.val();
            if (url != "") {
                mediaDrop.hide();
                preview.empty();
                preview.append(createMediaItem(url));
                preview.show();
            }
        }

        function configureDrop() {
            $(document).on('click', '#'+panel.attr('id')+' .media-drop span', function () {
                file.trigger('click');
            });
            $(document).on('change', '#'+panel.attr('id')+' #media-drop-file', function () {
                upload(file[0].files);
            });
            mediaDrop.on('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
            });
            mediaDrop.on('dragenter', function (e) {
                e.preventDefault();
                e.stopPropagation();
            });
            mediaDrop.on('drop', function (e) {
                if (e.originalEvent.dataTransfer) {
                    if (e.originalEvent.dataTransfer.files.length) {
                        e.preventDefault();
                        e.stopPropagation();
                        upload(e.originalEvent.dataTransfer.files);
                    }
                }
            });
        }

        function createMediaItem(url){
            var item = $('<div />').addClass('media-item').data('url',url).append([
                $('<img/>').addClass('media-item-image').attr('src',url),
                $('<span />').addClass('remove').append(
                    $('<i />').addClass('fa fa-times')
                )
            ]);

            if(params.cover != 'undefined' && params.cover)
                item.css({'background-size':'cover'});

            return item;
        }

        function upload(files) {
            var formData = new FormData();
            if(files.length > 1){
                toastr.error(params.errors.limitMultiFileUploads);
                return;
            }

            formData.append('file[]', files[0]);
            if(params.s != undefined)
                formData.append('s', params.s);

            $.ajax({
                url         : params.url,
                type        : 'POST',
                data        : formData,
                processData : false,
                contentType : false,
                success     : function (data) {
                    if (data.success) {
                        field.val(data.urls[0]);
                        mediaDrop.hide();
                        preview.append(createMediaItem(data.urls[0]));
                        preview.show();
                        file.resetElement();
                    } else {
                        toastr.error(data.message);
                    }
                }
            });
        }
    };

})(jQuery);
