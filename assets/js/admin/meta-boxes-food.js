jQuery( function( $ ) {

	// Food gallery file uploads.
	var food_gallery_frame;
	var $image_gallery_ids = $( '#food_image_gallery' );
	var $food_images       = $( '#food_images_container' ).find( 'ul.food_images' );

	$( '.add_food_images' ).on( 'click', 'a', function( event ) {
		var $el = $( this );

		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( food_gallery_frame ) {
			food_gallery_frame.open();
			return;
		}

		// Create the media frame.
		food_gallery_frame = wp.media.frames.food_gallery = wp.media({
			// Set the title of the modal.
			title: $el.data( 'choose' ),
			button: {
				text: $el.data( 'update' )
			},
			states: [
				new wp.media.controller.Library({
					title: $el.data( 'choose' ),
					filterable: 'all',
					multiple: true
				})
			]
		});

		// When an image is selected, run a callback.
		food_gallery_frame.on( 'select', function() {
			var selection = food_gallery_frame.state().get( 'selection' );
			var attachment_ids = $image_gallery_ids.val();

			selection.map( function( attachment ) {
				attachment = attachment.toJSON();

				if ( attachment.id ) {
					attachment_ids   = attachment_ids ? attachment_ids + ',' + attachment.id : attachment.id;
					var attachment_image = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

					$food_images.append( '<li class="image" data-attachment_id="' + attachment.id + '"><img src="' + attachment_image + '" /><ul class="actions"><li><a href="#" class="delete" title="' + $el.data('delete') + '">' + $el.data('text') + '</a></li></ul></li>' );
				}
			});

			$image_gallery_ids.val( attachment_ids );
		});

		// Finally, open the modal.
		food_gallery_frame.open();
	});

	// Image ordering.
	$food_images.sortable({
		items: 'li.image',
		cursor: 'move',
		scrollSensitivity: 40,
		forcePlaceholderSize: true,
		forceHelperSize: false,
		helper: 'clone',
		opacity: 0.65,
		placeholder: 'rp-metabox-sortable-placeholder',
		start: function( event, ui ) {
			ui.item.css( 'background-color', '#f6f6f6' );
		},
		stop: function( event, ui ) {
			ui.item.removeAttr( 'style' );
		},
		update: function() {
			var attachment_ids = '';

			$( '#food_images_container' ).find( 'ul li.image' ).css( 'cursor', 'default' ).each( function() {
				var attachment_id = $( this ).attr( 'data-attachment_id' );
				attachment_ids = attachment_ids + attachment_id + ',';
			});

			$image_gallery_ids.val( attachment_ids );
		}
	});

	// Remove images.
	$( '#food_images_container' ).on( 'click', 'a.delete', function() {
		$( this ).closest( 'li.image' ).remove();

		var attachment_ids = '';

		$( '#food_images_container' ).find( 'ul li.image' ).css( 'cursor', 'default' ).each( function() {
			var attachment_id = $( this ).attr( 'data-attachment_id' );
			attachment_ids = attachment_ids + attachment_id + ',';
		});

		$image_gallery_ids.val( attachment_ids );

		// Remove any lingering tooltips.
		$( '#tiptip_holder' ).removeAttr( 'style' );
		$( '#tiptip_arrow' ).removeAttr( 'style' );

		return false;
	});
});
