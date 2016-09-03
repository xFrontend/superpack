(function ($, wp) {
    'use strict';

    $(document).ready(function () {
        var media_frame;

        $(document).on('click', '.superpack__media-select', function (e) {
            e.preventDefault();

            var button_text = $(this).data('button-text') ? $(this).data('button-text') : 'Select',
                title = $(this).data('title') ? $(this).data('title') : '',
                filter = $(this).data('filter') ? $(this).data('filter') : '',
                is_multiple = $(this).data('multiple') ? true : false,
                container = $(this).parents('.superpack__uploader'),
                media_html = function (attachments) {
                    var html = '';

                    _.each(attachments, function (attachment) {
                        if ('image' === filter) {
                            html += '<img ';
                            html += ' src="' + attachment.url + '" ';
                            html += is_multiple ? ' width="106" ' : '';
                            html += '>';
                        }
                    });

                    return html;
                };

            if ('undefined' !== typeof(media_frame)) {
                media_frame.close();
            }

            media_frame = wp.media.frames.media_frame = wp.media({
                title: title,
                button: {text: button_text},
                library: {type: filter},
                multiple: is_multiple
            });

            media_frame.on('open', function () {
                var selection = media_frame.state().get('selection'),
                    ids = container.find('input').val(),
                    attachment;

                if (ids) {
                    ids = ids.split(',');
                    ids.forEach(function (id) {
                        attachment = wp.media.attachment(id);
                        selection.add(attachment ? [attachment] : []);
                    });
                }
            });

            media_frame.on('select', function () {
                var selection = media_frame.state().get('selection'),
                    ids = [],
                    preview = [];

                selection.map(function (attachment) {
                    attachment = attachment.toJSON();

                    preview.push(attachment);
                    ids.push(attachment.id);
                });

                container.find('.image-preview').html(media_html(preview));
                container.find('.superpack__media-clear').show();
                container.find('input').val(ids);
                container.find('input').trigger('change');
            });

            media_frame.open();
        });

        $(document).on('click', '.superpack__media-clear', function () {
            var container = $(this).parents('.superpack__uploader');

            container.find('input').val('');
            container.find('input').change();
            container.find('.image-preview').html('');

            $(this).hide();
        });
    });

})(jQuery, wp);
