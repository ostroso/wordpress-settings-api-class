<?php 
if ( !class_exists( 'Tadao_Settings_API' ) ):
	/**
	 * Extended class from the original
	 */
class Tadao_Settings_API extends WeDevs_Settings_API{
	
	/**
	 * Display a repeatable image field 
	 * @param  array $args settings field args
	 */
	function callback_media_image( $args ){
		$values =  $this->get_option( $args['id'], $args['section'], $args['std']	);
		$count = 0;
		echo sprintf('<div id="add-image-field-container-%1$s-%2$s" class="image-fields-container">', $args['section'], $args['id']);
		foreach ($values as $value){
			$html = sprintf('<div class="media-image-container" style="display: block;">'); 
			$html.= sprintf('<img class="%1$s-media-image" id="%2$s-%3$s-img" src="%4$s"/>', 'test', $args['section'], $args['id'], $this->get_image_by_ID($value), $count );
			$html.= sprintf('<input type="hidden" class="%1$s-media-image-hidden" id="%2$s-%3$s" name="%2$s[%3$s][]" value="%4$s"/>', 'test', $args['section'], $args['id'], $value , $count);
			$html.= sprintf('<input type="button" class="button button-primary add-image-button" id="%1$s-%2$s-button-add" value="Modifica" />', $args['section'], $args['id'] , $count);
			$html.=sprintf('<div id="%1$s-%2$s-button-delete" class="button button-primary remove-image-button">Rimuovi</div>', $args['section'], $args['id']);
			$html.='</div>';
			echo $html;
			$count++;
		}
		echo '</div>';
		echo sprintf('<div id="add-image-field-%1$s-%2$s" class="button button-primary add-image-field">Aggiungi</div>', $args['section'], $args['id']);

	}

};
endif;
?>