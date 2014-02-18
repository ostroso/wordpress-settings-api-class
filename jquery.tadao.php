<?php
// Contains additional javascript that will be inserted in jquery document.ready cycle 
ob_start();
?>
// Open the Media Manager
var _custom_media = true;
var _one_element = false;
// Set the original attachment behaviour
if (wp.media)
	var _orig_send_attachment = wp.media.editor.send.attachment;

// Disable the delete button if there's only one element
function check_remove_button() {
	if( $('.remove-image-button').length < 2){
		$('.remove-image-button').attr('disabled','disabled');
		_one_element = true;
	} else {
		_one_element = false;
	}
}

// Event: Remove image
$('.remove-image-button').on("click", function(e) {
	e.preventDefault();
	if(!_one_element){
		var button = $(this);
		var id = button.attr('id').replace('-button-delete','');
		var imgPlace = id+'-img';
		button.parent('.media-image-container').remove();
		check_remove_button();
	}
});

// Event: Add image field
$('.add-image-field').on('click', function(e){
	e.preventDefault();
	var button = $(this);
	var id = button.attr('id');
	var td = button.siblings('.image-fields-container');
	$('.media-image-container').eq(-1).clone(true, true).appendTo(td);
	console.log(td);
	// $('#submit').trigger('click');
});

// Event: add image
$('.add-image-button').on("click", function(e) {
	e.preventDefault();
	// Backup old functionality
	var send_attachment_bkp = wp.media.editor.send.attachment;
	var button = $(this);
	var id = button.attr('id').replace('-button-add', '');
	var attachmentID = "";
	// Flag for custom behaviour
	_custom_media = true;

	// Ovverride send.attachment functionality
	wp.media.editor.send.attachment = function(props, attachment) {
		if(_custom_media){
			// Set the value with the attachment's ID
			button.siblings("#"+id).val(attachment.id);
			attachmentID = attachment.id;
			// AJAX call to function
			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					action: 'get_image_by_ID',
					imgID: attachmentID
				},
				cache: false,
				success: function(response){
					var imgPlace = id+'-img';
					button.siblings("img#"+imgPlace).attr("src",response)
				}
			});
		} else {
			return _orig_send_attachment.apply( this, [props, attachment] );
		}
	};
	wp.media.editor.open(button);
	return false;
});
$('.image-fields-container').sortable();
check_remove_button();
<?php 
$content = ob_get_clean();
echo $content;
?>
