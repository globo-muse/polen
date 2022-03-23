(function($) {
    $('.polen-media-manager').on( 'click', function( e ) {
        e.preventDefault();

        var image_frame;
        if( image_frame ) {
            image_frame.open();
        }
        
        image_frame = wp.media({
            title: 'Selecione a Imagem',
            button: {
                text: 'Selecionar'
            },
            library : {
                type : [ 'image' ],
            },
            multiple : false,
        });

        image_frame.on('close',function() {
            let selection =  image_frame.state().get('selection');
            selection.each( function( attachment ) {
                let attachment_data = attachment['attributes'];
                $('.polen-input-image-id').val( attachment_data['id'] );
                $('.polen-input-image-url').val( attachment_data['url'] );
                $('.polen-input-image-thumb').val( attachment_data['sizes']['thumbnail']['url'] );
                let image_tag = '<img src="' + attachment_data['sizes']['thumbnail']['url'] + '" alt="Imagem de capa">';
                $('.polen-image-gallery-data').html( image_tag );
            });
        });

        image_frame.on( 'open',function() {
            if( $('#nfImageId').val() != '' ) {
                let selection =  image_frame.state().get('selection');
                let id = $('.polen-input-image-id').val();
                let attachment = wp.media.attachment( id );
                attachment.fetch();
                selection.add( attachment ? [ attachment ] : [] );
            }
        });

        image_frame.open();
    });
})(jQuery);