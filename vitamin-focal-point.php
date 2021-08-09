<?php
/**
 * Plugin Name: Vitamin Focal Point
 * Plugin URI: https://vitaminisgood.com
 * Description:  Enables option to set a focal point for images via the media library. Requires manual integration with frontend code.
 * Version: 1.0.0
 * Author: Vitamin
 * Author URI: https://vitaminisgood.com
 * GitHub Plugin URI: vitamin-dev/vitamin-focal-point
 *
 * @package Vitamin\Plugins\Focal_Point
 * @author Vitamin
 */

/**
 * Add focal point settings
 *
 * @param array   $form_fields Image settings fields
 * @param WP_Post $post        WP Attachment
 * @return array
 */
function v_add_focal_point_setting( $form_fields, $post ) {
	$focal_x           = esc_attr( get_post_meta( $post->ID, '_focal-point-x', true ) );
	$focal_y           = esc_attr( get_post_meta( $post->ID, '_focal-point-y', true ) );
	$focal_string      = $focal_x && $focal_y ? $focal_x . '%, ' . $focal_y . '%' : 'No focal point set';
	$focal_reset_style = $focal_x && $focal_y ? '' : 'style="display: none"';

	if ( 'image/png' !== $post->post_mime_type && 'image/jpeg' !== $post->post_mime_type && 'image/jpg' !== $post->post_mime_type ) {
		return $form_fields;
	}

	$form_fields['focal-point'] = [
		'label' => __( 'Focal Point' ),
		'input' => 'html',
		'html'  => '
			<button type="button" class="focal-point-trigger button-primary button-large">Focal Point</button>
			<p>
				<span class="focal-point-preview focal-point-preview--website" style="display: block;">' . $focal_string . '</span>

				<a href="#" class="clear-website-focal-points" ' . $focal_reset_style . '>Clear Focal Point</a>

				<input id="attachments-' . $post->ID . '-focal-point-x" type="hidden" value="' . $focal_x . '" name="attachments[' . $post->ID . '][focal-point-x]">
				<input id="attachments-' . $post->ID . '-focal-point-y" type="hidden" value="' . $focal_y . '" name="attachments[' . $post->ID . '][focal-point-y]">
			</p>
		',
		'value' => '',
	];

	return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'v_add_focal_point_setting', null, 2 );

/**
 * Save focal point settings
 *
 * @param WP_Post $post       WP Attachment
 * @param array   $attachment Submitted settings
 * @return WP_Post
 */
function v_add_focal_point_to_save( $post, $attachment ) {
	update_post_meta( $post['ID'], '_focal-point-x', esc_attr( $attachment['focal-point-x'] ) );
	update_post_meta( $post['ID'], '_focal-point-y', esc_attr( $attachment['focal-point-y'] ) );

	return $post;
}
add_filter( 'attachment_fields_to_save', 'v_add_focal_point_to_save', null, 2 );

/**
 * Make focal point selector
 */
function v_handle_focal_points() {
	?>
	<script>
		(function($){
			$(function(){
				$('body').append("<style>.v-focal-modal{position:fixed;top:0;left:0;width:100%;height:100%;z-index:999999;display:none}.v-focal-modal.open{display:block}.v-focal-modal__inner{position:absolute;left:15%;right:15%;top:100px;bottom:100px}.v-focal-modal__content{background:#fcfcfc;height:100%;min-height:300px;overflow-x:hidden;overflow-y:auto;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column}.v-focal-modal__instructions{padding:0 16px;margin:-8px 0 16px}.v-focal-modal__img-wrap{width:100%;-webkit-box-flex:1;-ms-flex-positive:1;flex-grow:1;-ms-flex-negative:1;flex-shrink:1}.v-focal-modal__img-inner{display:-webkit-box;display:-ms-flexbox;display:flex;width:100%;height:100%;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;-webkit-box-align:center;-ms-flex-align:center;align-items:center}.v-focal-modal__line-wrap{display:inline;position:relative}.v-focal-modal__line{position:absolute;top:0;left:0}.v-focal-modal__line--x{height:100%;border-left:1px solid red}.v-focal-modal__line--y{width:100%;border-bottom:1px solid red}.v-focal-modal__img{-webkit-box-sizing:border-box;box-sizing:border-box;display:block;max-width:100%;margin:0 auto;border:1px solid red}.v-focal-modal .media-frame-title{position:static;width:100%;-webkit-box-flex:0;-ms-flex-positive:0;flex-grow:0;-ms-flex-negative:0;flex-shrink:0;height:auto;-webkit-box-shadow:0 10px 10px -10px rgba(0,0,0,.15);box-shadow:0 10px 10px -10px rgba(0,0,0,.15)}@media screen and (max-width:900px){.v-focal-modal__inner{top:30px;right:30px;bottom:30px;left:30px}}@media screen and (max-width:640px){.v-focal-modal__inner{top:0;right:0;bottom:0;left:0}}</style>");

				function createModal() {
					var modal = $('<div>');
					modal.addClass('v-focal-modal open');
					modal.appendTo($('body'));

					var modalInner = $('<div>');
					modalInner.addClass('v-focal-modal__inner');
					modalInner.appendTo(modal);

					var modalContent = $('<div>');
					modalContent.addClass('v-focal-modal__content');
					modalContent.appendTo(modalInner);

					var close = $('<button type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text">Close dialog</span></span></button>');
					close.appendTo(modalContent);
					close.click(destroyModal);

					var titleWrap = $('<div>');
					titleWrap.addClass('media-frame-title');
					titleWrap.appendTo(modalContent);

					var title = $('<h1>Set Focal Point</h1>');
					title.appendTo(titleWrap);

					var instructions = $('<p>Click on the image to set its focal point.</p>');
					instructions.addClass('v-focal-modal__instructions')
					instructions.appendTo(titleWrap);

					var imgWrap = $('<div>');
					imgWrap.addClass('v-focal-modal__img-wrap');
					imgWrap.appendTo(modalContent);

					var imgInner = $('<div>');
					imgInner.addClass('v-focal-modal__img-inner');
					imgInner.appendTo(imgWrap);

					var lineWrap = $('<div>');
					lineWrap.addClass('v-focal-modal__line-wrap');
					lineWrap.appendTo(imgInner);
					lineWrap.on('mousemove', focalFollowPointer);
					lineWrap.on('mouseleave', focalLeaveWrap);
					lineWrap.on('click', setFocalPoint);

					var lineX = $('<div>');
					lineX.addClass('v-focal-modal__line v-focal-modal__line--x');
					lineX.appendTo(lineWrap);
					var initFocalLeft = $('.attachment-info [name*="focal-point-x"]').val() || $('.media-sidebar [name*="focal-point-x"]').val() || 0;
					lineX.css('left', initFocalLeft + '%');

					var lineY = $('<div>');
					lineY.addClass('v-focal-modal__line v-focal-modal__line--y');
					lineY.appendTo(lineWrap);
					var initFocalTop = $('.attachment-info [name*="focal-point-y"]').val() || $('.media-sidebar [name*="focal-point-y"]').val() || 0;
					lineY.css('top', initFocalTop + '%');

					var img = $('<img>');
					var imgSrc = $('input[id$="-copy-link"]').val();
					img.attr('src', imgSrc);
					img.addClass('v-focal-modal__img');
					img.appendTo(lineWrap);

					$('.media-modal').css('display', 'none');
				}

				function destroyModal() {
					var modal = $('.v-focal-modal');
					modal.remove();
					$('.media-modal').css('display', 'block');
				}

				var focalSetLeft,
						focalSetTop;

				function focalFollowPointer(e) {
					var lineX = $('.v-focal-modal__line--x');
					var lineY = $('.v-focal-modal__line--y');
					var wrap = $('.v-focal-modal__line-wrap');

					var wrapOffset = wrap[0].getBoundingClientRect();

					var cursorLeft = e.clientX - wrapOffset.left;
					var cursorTop = e.clientY - wrapOffset.top;

					var wrapWidth = wrap.outerWidth();
					var wrapHeight = wrap.outerHeight();

					lineX.css('left', (cursorLeft / wrapWidth * 100).toFixed(4) + '%');
					lineY.css('top', (cursorTop / wrapHeight * 100).toFixed(4) + '%');

					focalSetLeft = (cursorLeft / wrapWidth * 100).toFixed(4);
					focalSetTop = (cursorTop / wrapHeight * 100).toFixed(4);
				}

				function focalLeaveWrap() {
					var lineX = $('.v-focal-modal__line--x');
					var lineY = $('.v-focal-modal__line--y');
					var initFocalLeft = $('.attachment-info [name*="focal-point-x"]').val() || $('.media-sidebar [name*="focal-point-x"]').val() || 0;
					var initFocalTop = $('.attachment-info [name*="focal-point-y"]').val() || $('.media-sidebar [name*="focal-point-y"]').val() || 0;

					lineX.css('left', initFocalLeft + '%');
					lineY.css('top', initFocalTop + '%');
				}

				function setFocalPoint() {
					var wrap = $('.v-focal-modal__line-wrap');
					var img = $('.v-focal-modal__img');

					var xInput = $('.attachment-info [name*="focal-point-x"]').length ? $('.attachment-info [name*="focal-point-x"]') : $('.media-sidebar [name*="focal-point-x"]');
					var yInput = $('.attachment-info [name*="focal-point-y"]').length ? $('.attachment-info [name*="focal-point-y"]') : $('.media-sidebar [name*="focal-point-y"]');

					$('.focal-point-preview--website').html('Website: ' + focalSetLeft + '%, ' + focalSetTop + '%');
					$('.clear-website-focal-points').css('display', 'inline');

					xInput.val(focalSetLeft);
					yInput.val(focalSetTop).change();

					img.animate({opacity: .85}, 50).animate({opacity: 1}, 50);

					destroyModal();
				}

				$('body').on('click', '.focal-point-trigger', createModal);

				$('body').on('click', '.clear-website-focal-points', function(e){
					e.preventDefault();
					var xInput = $('.attachment-info [name*="focal-point-x"]').length ? $('.attachment-info [name*="focal-point-x"]') : $('.media-sidebar [name*="focal-point-x"]');
					var yInput = $('.attachment-info [name*="focal-point-y"]').length ? $('.attachment-info [name*="focal-point-y"]') : $('.media-sidebar [name*="focal-point-y"]');

					xInput.val('');
					yInput.val('').change();
					$('.focal-point-preview--website').html('No focal point set');
				});
			});
		})(jQuery)
	</script>
	<?php
}
add_action( 'admin_head', 'v_handle_focal_points' );
