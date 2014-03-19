<?php
// Contains additional javascript that will be inserted in jquery document.ready cycle 
ob_start();
?>
// Open the Media Manager
var _custom_media = false;
var _custom_link = false;
var _one_element = false;
var $linkFieldDialog = $('#link-field-dialog');
var currentLinkField = false;
var $linkFieldSearch = $("#link-field-search");


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
});

$linkFieldDialog.dialog({
	autoOpen: false,
	show: {
		effect: "blind",
		duration: 1000
	},
	hide: {
		effect: "blind",
		duration: 1000
	},
	width: 500,
	modal: true,
	buttons: {
		"Collega Risorsa": function(){
			console.log(currentLinkField);
			urlValue = $('li.selected',$linkFieldDialog).data('url');
			nameValue = $('li.selected span.resource-title',$linkFieldDialog).html();
			$('#'+currentLinkField.replace('-link-button', '-url')).val(urlValue);
			$('#'+currentLinkField.replace('-link-button', '-span')).html(nameValue);
			currentLinkField = false;
			$(this).dialog('close');
			$("li", $linkFieldDialog).removeClass('selected');
		}
	},
	close: function(){
		$("li", $linkFieldDialog).removeClass('selected');
	}
});
$.ajax({
	url: ajaxurl,
	type: 'post',
	data: {
		action: 'get_resources'
	},
	dataType:"json",
	cache: false,
	success: function(response){
		$.each(response, function(index, value){
			console.log(value.name);
			if(value.id.indexOf("type:") != -1){
				$('<li data-url="'+value.id+'"><span class="resource-title">'+value.name+'</span><span class="resource-info"></span></li>').appendTo('#link-field-dialog ul');
			} else if(value.id.indexOf("category:") != -1){
				$('<li data-url="'+value.id+'"><span class="resource-title">'+value.name+'</span><span class="resource-info"></span></li>').appendTo('#link-field-dialog ul');
			}else {
				$('<li data-url="'+value.id+'"><span class="resource-title">'+value.name+'</span><span class="resource-info"></span></li>').appendTo('#link-field-dialog ul');
			}
		});
		$("li", $linkFieldDialog).on('click', function(e){
			console.log('click');
			e.preventDefault();
			$("li", $linkFieldDialog).removeClass('selected');
			$(this).addClass('selected');
		});
	}
});


$linkFieldSearch.keyup(function(){
	var filter = $linkFieldSearch.val();
	$("#link-field-dialog ul li").each(function(){
		if ($(this).text().search(new RegExp(filter, "i")) < 0) {
			$(this).fadeOut();
		} else {
			$(this).show();
		}
	});
});

$('.link-image-button').on('click', function(e){
	currentLinkField = $(this).attr("id");
	$linkFieldDialog.dialog('open');
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
