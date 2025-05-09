<?php
/**
* Content Full Width (Custom)
*/
class Fh_Content_Fullalt_Bgimage_Widget extends WP_Widget {
	/**
	* Register widget with WordPress
	*/
	function __construct() {
		parent::__construct(
			'fh_content_fullalt_bgimage_widget',
			__('500 Content Wide (custom)', 'fivehundred'),
			array('description' => __('A full browser-width content area. Fully customizable. Add images and background images, control text and background color, and more.', 'fivehundred')),
			array('width' => 'auto'));
	}
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget($args, $instance) {
		if (!empty($instance)) {
			if (isset($instance['title'])) {
				$title = $instance['title'];
			}
			else {
				$title = '';
			}
			if (isset($instance['height'])) {
				$height = html_entity_decode($instance['height']);
			}
			else {
				$height = 100;
			}
			if (isset($instance['text'])) {
				$text = html_entity_decode($instance['text']);
			}
			else {
				$text = '';
			}
			if (isset($instance['align'])) {
				$align = $instance['align'];
			}
			else {
				$align = 'bg_imageleft';
			}
			if (isset($instance['image'])) {
				$image = html_entity_decode($instance['image']);
			}
			else {
				$image = '';
			}
			if (isset($instance['bgimage'])) {
				$bgimage = html_entity_decode($instance['bgimage']);
			}
			else {
				$bgimage = '';
			}
			if (isset($instance['padding_top'])) {
				$padding_top = html_entity_decode($instance['padding_top']);
			}
			else {
				$padding_top = '';
			}
			if (isset($instance['padding_bottom'])) {
				$padding_bottom = html_entity_decode($instance['padding_bottom']);
			}
			else {
				$padding_bottom = '';
			}
			if (isset($instance['text_color'])) {
				$text_color = html_entity_decode($instance['text_color']);
			}
			else {
				$text_color = '';
			}
			if (isset($instance['bg_color'])) {
				$bg_color = html_entity_decode($instance['bg_color']);
			}
			else {
				$text_color = '';
			}
			if (isset($instance['custom_class'])) {
				$custom_class = html_entity_decode($instance['custom_class']);
			}
			else {
				$custom_class = '';
			}
			$id = $args['widget_id'];
			$idpos = stripos($id, '-');
			$num_id = substr($id, $idpos + 1);
			if ($num_id & '1') {
				$oddeven = 'even';
			}
			else {
				$oddeven = 'odd';
			}
			echo '<div class="fh_widget fullwindow custom '.$align.' ';
			echo (!empty($custom_class) ? $custom_class : ''); 
			echo '" style="';
			echo (!empty($height) ? 'height: '.$height.'px;' : '');
			echo (!empty($text_color) ? 'color: '.$text_color.' !important;' : '');
			echo '" id="'.$id.'"><div class="fullwindow-internal cf '.$oddeven.'" style="';
			echo (!empty($bgimage) ? 'background-image: url('.$bgimage.');' : '');
			echo (!empty($bg_color) ? ' background-color: '.$bg_color.';' : '');   
			echo '"><div class="constrained"><div class="ign-content-fullalt" style="';	
			echo (!empty($height) ? 'height: '.$height.'px;' : '');		
			echo (!empty($padding_top) ? ' padding-top: '.$padding_top.'px;' : ''); 
			echo (!empty($padding_bottom) ? ' padding-bottom: '.$padding_bottom.'px;' : '');
			echo '">';
			echo (!empty($image) ? '<div class="widgetimage"><img src="'.$image.'"></div>' : '');
			echo '<div class="ign-text-container" style="color: '.$text_color.'">';
			echo (!empty($title) ? '<h3 class="widget-title" style="color: '.$text_color.'">'.$title.'</h3>' : '');
			echo (!empty($text) ? '<div class="ign-content-text" style="color: '.$text_color.'">'.$text.'</div>' : '');
			echo '</div></div></div></div></div>';
		}
	}
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	function form($instance) {
		if (isset($instance['height'])) {
			$height = $instance['height'];
		}
		if (isset($instance['image'])) {
			$image = $instance['image'];
		}
		if (isset($instance['bgimage'])) {
			$bgimage = $instance['bgimage'];
		}
		if (isset($instance['text'])) {
			$text = $instance['text'];
		}
		if (isset($instance['title'])) {
			$title = $instance['title'];
		}
		if (isset($instance['align'])) {
			$align = $instance['align'];
		}
		else {
			$align = '';
		}
		if (isset($instance['padding_top'])) {
			$padding_top = $instance['padding_top'];
		}
		if (isset($instance['padding_bottom'])) {
			$padding_bottom = $instance['padding_bottom'];
		}
		if (isset($instance['text_color'])) {
			$text_color = $instance['text_color'];
		}
		if (isset($instance['bg_color'])) {
			$bg_color = $instance['bg_color'];
		}
		if (isset($instance['custom_class'])) {
			$custom_class = $instance['custom_class'];
		}
		
		$form = '<p>';
		$form .= '<label for="'.$this->get_field_id( 'title' ).'">'.__('Title (optional)', 'fivehundred').':';
		$form .= '<input class="widefat" type="text" id="'.$this->get_field_id( 'title' ).'" name="'.$this->get_field_name( 'title' ).'" value="'.(isset($title) ? $title : '').'"/>';
		$form .= '</label></p>';
		$form .= '<p><label for="'.$this->get_field_id( 'height' ).'">'.__('Content Height (numbers only, measured in pixels)', 'fivehundred').':';
		$form .= '<input type="text" class="widefat" id="'.$this->get_field_id( 'height' ).'" name="'.$this->get_field_name( 'height' ).'" value="'.(isset($height) ? $height : '').'">';
		$form .= '</label><span style="font-size: 90%; color: #666;">'.__('This must be set to ensure content below it does not roll up underneath it.', 'fivehundred').'</span></p>';
		$form .= '<p>';
		$form .= '<select id="'.$this->get_field_id( 'align' ).'" name="'.$this->get_field_name( 'align' ).'">
				    <option value="" disabled="disabled" selected="selected">'.__('Please Select a Layout', 'fivehundred').'</option>
				    <option value="bg_imageleft" '.(isset($align) && $align == 'bg_imageleft' ? 'selected="selected"' : '').'>'.__('Image Left', 'fivehundred').'</option>
				    <option value="bg_imageright" '.(isset($align) && $align == 'bg_imageright' ? 'selected="selected"' : '').'>'.__('Image Right', 'fivehundred').'</option>
				    <option value="bg_imagecenter" '.(isset($align) && $align == 'bg_imagecenter' ? 'selected="selected"' : '').'>'.__('Image and Text Center', 'fivehundred').'</option>
				    <option value="bg_textleft" '.(isset($align) && $align == 'bg_textleft' ? 'selected="selected"' : '').'>'.__('No Image and Text Left', 'fivehundred').'</option>
				    <option value="bg_textright" '.(isset($align) && $align == 'bg_textright' ? 'selected="selected"' : '').'>'.__('No Image and Text Right', 'fivehundred').'</option>
				  </select>';
		$form .= '</p>';
		$form .= '<div style="width: 49.5%; display: inline-block; margin-right: 1%;">';
		$form .= '<label for="'.$this->get_field_id( 'image' ).'">'.__('Image (optional)', 'fivehundred').':</label>';
		$form .= '<input type="text" class="widefat alert-image" id="'.$this->get_field_id( 'image' ).'" name="'.$this->get_field_name( 'image' ).'" value="'.(!empty($image) ? $image : '').'">';
		$form .= '<button class="button fh_media_button" name="fh_media_button" id="'.$this->get_field_id( 'media' ).'">'.__('Add Image', 'fivehundred').'</button>';
		$form .= '</div>';
		$form .= '<div style="width: 49.5%; display: inline-block;">';
		$form .= '<label for="'.$this->get_field_id( 'bgimage' ).'">'.__('Background Image (optional)', 'fivehundred').':</label>';
		$form .= '<input type="text" class="widefat bg-image" id="'.$this->get_field_id( 'bgimage' ).'" name="'.$this->get_field_name( 'bgimage' ).'" value="'.(!empty($bgimage) ? $bgimage : '').'">';
		$form .= '<button class="button fh_media_button2" name="fh_media_button2" id="'.$this->get_field_id( 'media' ).'">'.__('Add Background Image', 'fivehundred').'</button>';
		$form .= '</div>';
		$form .= '<p>';
		$form .='<div style="width: 49.5%; display: inline-block; margin-right: 1%;"><label for="'.$this->get_field_id( 'text_color' ).'">'.__('Text Color (optional)', 'fivehundred').':';
		$form .= '<input class="widefat" type="text" id="'.$this->get_field_id( 'text_color' ).'" name="'.$this->get_field_name( 'text_color' ).'" value="'.(isset($text_color) ? $text_color : '').'"/></div>';
		$form .='<div style="width: 49.5%; display: inline-block;"><label for="'.$this->get_field_id( 'bg_color' ).'">'.__('Background Color (optional)', 'fivehundred').':';
		$form .= '<input class="widefat" type="text" id="'.$this->get_field_id( 'bg_color' ).'" name="'.$this->get_field_name( 'bg_color' ).'" value="'.(isset($bg_color) ? $bg_color : '').'"/></div>';
		$form .= '<div style="font-size: 90%; color: #666; margin-bottom: 10px; text-align: center;">Colors must be represented as Hex or RGBA <br> ie: #ffffff or rgba(255,255,255, .5)</div>';
		$form .= '<div style="width: 49.5%; display: inline-block; margin-right: 1%;"><label for="'.$this->get_field_id( 'padding_top' ).'">'.__('Padding Top (optional)', 'fivehundred').':';
		$form .= '<input class="widefat" type="text" id="'.$this->get_field_id( 'padding_top' ).'" name="'.$this->get_field_name( 'padding_top' ).'" value="'.(isset($padding_top) ? $padding_top : '').'"/></div>';
		$form .= '<div style="width: 49.5%; display: inline-block;"><label for="'.$this->get_field_id( 'padding_bottom' ).'">'.__('Padding Bottom (optional)', 'fivehundred').':';
		$form .= '<input class="widefat" type="text" id="'.$this->get_field_id( 'padding_bottom' ).'" name="'.$this->get_field_name( 'padding_bottom' ).'" value="'.(isset($padding_bottom) ? $padding_bottom : '').'"/>';
		$form .= '</div><div style="font-size: 90%; color: #666; text-align: center; margin-bottom: 10px;">'.__('Numbers only, measured in pixels.', 'fivehundred').'</div>';
		$form .= '<textarea class="widefat" rows="16" cols="20" id="'.$this->get_field_id( 'text' ).'" name="'.$this->get_field_name( 'text' ).'">';
		$form .= (!empty($text) ? $text : '');
		$form .= '</textarea>';
		$form .='<label for="'.$this->get_field_id( 'custom_class' ).'">'.__('Custom Class Name  (optional)', 'fivehundred').':';
		$form .= '<input class="widefat" type="text" id="'.$this->get_field_id( 'custom_class' ).'" name="'.$this->get_field_name( 'custom_class' ).'" value="'.(isset($custom_class) ? $custom_class : '').'"/>';
		$form .= '<div style="font-size: 90%; color: #666; text-align: center; margin-bottom: 10px;">'.__('you may add multiple classes by seperating with spaces <br> (ie: class class_two)', 'fivehundred').'</div>';
		echo $form;
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['height'] = esc_attr(strip_tags($new_instance['height']));
		$instance['title'] = esc_attr(strip_tags($new_instance['title']));
		$instance['align'] = esc_attr($new_instance['align']);
		$instance['text'] = esc_attr($new_instance['text']);
		$instance['image'] = esc_attr($new_instance['image']);
		$instance['bgimage'] = esc_attr($new_instance['bgimage']);
		$instance['padding_top'] = esc_attr($new_instance['padding_top']);
		$instance['padding_bottom'] = esc_attr($new_instance['padding_bottom']);
		$instance['text_color'] = esc_attr($new_instance['text_color']);
		$instance['bg_color'] = esc_attr($new_instance['bg_color']);
		$instance['custom_class'] = esc_attr($new_instance['custom_class']);
		return $instance;
	}
}
?>