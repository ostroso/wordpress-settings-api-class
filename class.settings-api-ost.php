<?php 
if ( !class_exists( 'Tadao_Settings_API' ) ):
	/**
	 * Extended class from the original
	 */
class Tadao_Settings_API extends WeDevs_Settings_API{
	

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action('wp_ajax_get_image_by_ID', array( $this, 'get_image_by_ID_callback') );

	}

	public function link_field_dialog()
	{
		$html = '<div id="link-field-dialog" title="Collegamento" class="link-field-dialog">';
		$html.='<p class="howto">Puoi filtrare i risultati con il campo di ricerca</p>';
		$html.='<label><span class="search-label">Cerca</span>';
		$html.='<input type="search" id="link-field-search" autocomplete="off" class="link-search-field" /></label>';
		$html.="<ul>";
		$html.="</ul>";
		$html.='</div>';
		return $html;
	}

	/**
  * Enqueue scripts and styles
  */
	function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style("wp-jquery-ui-dialog");
		wp_enqueue_style( 'tadao-settings-api-css', plugins_url( 'api-style.css', __FILE__ ) );
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'editorremov' );
		wp_enqueue_script( 'editor-functions' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'wpdialogs' );
    	// Needed for media uploader
		wp_enqueue_media();
	}


  /**
   * Ajax call to obtain image src from ID
   * @return void
   */
  function get_image_by_ID_callback($imgID = false) {
  	global $wpdb;
  	if (!$imgID)
  		$imgID = ( $_POST['imgID'] );
  	$img_thumb = wp_get_attachment_image_src( $imgID, 'medium');
  	echo $img_thumb[0];
  	die(); 
  }

  /**
   * Obtain image src from ID
   * @return void
   */
  public function get_image_by_ID($imgID = false) {
  	global $wpdb;
  	$img_thumb = wp_get_attachment_image_src( $imgID, 'medium');
  	return $img_thumb[0];
  }

	/**
	 * Display a link field with customizable image
	 * @param  array $args settings field args
	 * 
	 */
	function callback_image_link( $args ){
		$html = "<style>#wp-nope-wrap{display:none;}</style>";
		
		$value = $this->get_option( $args['id'], $args['section'], $args['std']	);
		$html.= '<div class="image-link-container">';
		$html.='';
		$html.='<table><tr>';
		$html.='<td width="300">';
		$html.= sprintf('<input type="hidden" class="%1$s-link-hidden" id="%2$s-%3$s-url" name="%2$s[%3$s][url]" value="%4$s"/>', 'image', $args['section'], $args['id'], $value['url'] );
		$show_value = $this->get_url_name($value['url']);
		$html.= sprintf('<p>Link a: <span name="%2$s[%3$s][urlName]" id="%2$s-%3$s-span">%1$s</span></p>', $show_value, $args['section'], $args['id'] );
		$html.='</td>';
		$html.='<td>';
		$html.= sprintf('<input type="button" class="button button-primary link-image-button" id="%1$s-%2$s-link-button" value="Modifica Link" />', $args['section'], $args['id'] );
		$html.='</td>';
		$html.='</tr>';
		$html.='<tr>';
		$html.='<td>';
		$html.= sprintf('<img class="%1$s-media-image" id="%2$s-%3$s-img" src="%4$s"/>', 'thumb', $args['section'], $args['id'], $this->get_image_by_ID($value['img']) );
		$html.= sprintf('<input type="hidden" class="%1$s-media-image-hidden" id="%2$s-%3$s" name="%2$s[%3$s][img]" value="%4$s"/>', 'image-id', $args['section'], $args['id'], $value['img'] );
		$html.='</td>';
		$html.='<td>';
		$html.= sprintf('<input type="button" class="button button-primary add-image-button" id="%1$s-%2$s-button-add" value="Modifica Immagine" />', $args['section'], $args['id'] );
		$html.='</td>';
		$html.='</tr>';
		$html.='<tr>';
		$html.='<td>';
		$html.='Testo:';
		$html.='</td>';
		$html.='<td>';
		$html.= sprintf('<input type="text" class="%1$s-media-image" id="%2$s-%3$s-text" name="%2$s[%3$s][text]" value="%4$s"/>', 'text', $args['section'], $args['id'], $value['text'] );
		$html.='</td>';
		$html.='</tr>';
		$html.='</table>';
		$html.= '</div>';
		echo $html;
	}

	public function get_url_name($id)
	{
		if( strpos($id, 'type:') !== false ){
			$post_type_label = get_post_type_object(str_replace('type:','', $id));
			return 'Archivio '.$post_type_label->labels->name;
		} 
		elseif( strpos($id, 'category:') !== false ){
			$ids_string = str_replace('category:','', $id);
			$ids = explode(';', $ids_string);
			$cat_object = get_term($ids[0],$ids[1]);
			return 'Categoria '.$cat_object->name;
		}
		elseif( strpos($id, 'site:') !== false ){
			$site_id = str_replace('site:','', $id);
			$site_object = get_blog_details($site_id);
			return 'Sito '.$site_object->blogname;
		}
		else {
			$post = get_post(intval($id));
			$post_type_slug = $post->post_type;
			$post_type_object = get_post_type_object($post_type_slug);

			return $post->post_title.' ( '.$post_type_object->labels->singular_name.' )';
		}
	}

	/**
	 * Display a repeatable image field 
	 * @param  array $args settings field args
	 */
	function callback_media_image( $args ){
		$values =  $this->get_option( $args['id'], $args['section'], $args['std']	);
		echo sprintf('<div id="image-field-container-%1$s-%2$s" class="image-fields-container" style="cursor:move;">', $args['section'], $args['id']);
		if(empty($values)){
			$values = array(8);
		}
		foreach ($values as $value){
			$html = sprintf('<div class="media-image-container" style="display: block;">'); 
			$html.= sprintf('<img class="%1$s-media-image" id="%2$s-%3$s-img" src="%4$s"/>', 'repeatable', $args['section'], $args['id'], $this->get_image_by_ID($value) );
			$html.= sprintf('<input type="hidden" class="%1$s-media-image-hidden" id="%2$s-%3$s" name="%2$s[%3$s][]" value="%4$s"/>', 'repeatable', $args['section'], $args['id'], $value );
			$html.= sprintf('<input type="button" class="button button-primary add-image-button" id="%1$s-%2$s-button-add" value="Modifica" />', $args['section'], $args['id'] );
			$html.=sprintf('<div id="%1$s-%2$s-button-delete" class="button button-primary remove-image-button">Rimuovi</div>', $args['section'], $args['id']);
			$html.='</div>';
			echo $html;
		}
		echo '</div>';
		echo sprintf('<div id="add-image-field-%1$s-%2$s" class="button button-primary add-image-field">Aggiungi</div>', $args['section'], $args['id']);

	}

	/**
    * Displays a textarea for a settings field (Override fot html save)
    *
    * @param array   $args settings field args
    */
	function callback_textarea( $args ) {

		$value = html_entity_decode( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]">%4$s</textarea>', $size, $args['section'], $args['id'], $value );
		$html .= sprintf( '<br><span class="description"> %s</span>', $args['desc'] );

		echo $html;
	}

};
endif;
?>