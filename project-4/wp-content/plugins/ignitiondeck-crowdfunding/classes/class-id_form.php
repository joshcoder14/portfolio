<?php

class ID_Form {

	var $fields;

	function __construct(
		$fields = null
		) {
		 $this->fields = $fields;
	}

	function build_form( $vars = null ) {
		$output = '<div>';
		foreach ( $this->fields as $field ) {
			if ( isset( $field['label'] ) ) {
				$label = $field['label'];
			} else {
				$label = '';
			}
			if ( isset( $field['name'] ) ) {
				$name = $field['name'];
			} else {
				$name = '';
			}
			if ( isset( $field['id'] ) ) {
				$id = $field['id'];
			} else {
				$id = null;
			}
			if ( isset( $field['wclass'] ) ) {
				$wclass = $field['wclass'];
			} else {
				$wclass = null;
			}
			if ( isset( $field['class'] ) ) {
				$class = $field['class'];
			} else {
				$class = $id;
			}
			if ( isset( $field['type'] ) ) {
				$type = $field['type'];
			} else {
				$type = '';
			}
			if ( isset( $field['options'] ) ) {
				$options = $field['options'];
			} else {
				$options = null;
			}
			if ( isset( $field['value'] ) ) {
				$value = apply_filters( 'idcf_fes_' . $name . '_value', $field['value'], ( isset( $vars['post_id'] ) ? $vars['post_id'] : null ) );
			} else {
				$value = null;
			}
			if ( isset( $field['misc'] ) ) {
				$misc = $field['misc'];
			} else {
				$misc = '';
			}
			// Start Building
			ob_start();
			$post_id = ( isset( $vars['post_id'] ) ? $vars['post_id'] : null );
			do_action( 'fes_' . $name . '_before', $post_id );
			$output .= ob_get_contents();
			ob_end_clean();
			if ( isset( $field['before'] ) ) {
				$output .= $field['before'];
			}
			$output .= '<div class="form-row ' . $name . '' . ( isset( $wclass ) ? ' ' . $wclass : '' ) . '">';
			// #devnote we can move some of these into an iterative loop
			switch ( $type ) {
				case 'text':
					if ( ! empty( $label ) ) {
						$output .= '<p><label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label>';
					}
					$output .= '<input type="text" id="' . $id . '" name="' . $name . '" class="' . $class . '" value="' . $value . '" ' . $misc . '/>';
					if ( ! empty( $label ) ) {
						$output .= '</p>';
					}
					break;
				case 'email':
					if ( ! empty( $label ) ) {
						$output .= '<p><label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label>';
					}
					$output .= '<input type="email" id="' . $id . '" name="' . $name . '" class="' . $class . '" value="' . $value . '" ' . $misc . '/>';
					if ( ! empty( $label ) ) {
						$output .= '</p>';
					}
					break;
				case 'number':
					if ( ! empty( $label ) ) {
						$output .= '<p><label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label>';
					}
					$output .= '<input type="number" id="' . $id . '" name="' . $name . '" class="' . $class . ' number-field" value="' . ( ( ! empty( $value ) ) ? $value : 0 ) . '" ' . $misc . '/>';
					if ( ! empty( $label ) ) {
						$output .= '</p>';
					}
					break;
				case 'password':
					if ( ! empty( $label ) ) {
						$output .= '<p><label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label>';
					}
					$output .= '<input type="password" id="' . $id . '" name="' . $name . '" class="' . $class . '" value="' . $value . '" ' . $misc . '/>';
					if ( ! empty( $label ) ) {
						$output .= '</p>';
					}
					break;
				case 'file':
					if ( ! empty( $label ) ) {
						$output .= '<p><label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label>';
					}
					$output .= '<input type="file" id="' . $id . '" name="' . $name . '" class="' . $class . '" value="' . $value . '" ' . $misc . '/>';
					if ( ! empty( $label ) ) {
						$output .= '</p>';
					}
					break;
				case 'date':
					if ( ! empty( $label ) ) {
						$output .= '<p><label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label>';
					}
					$output .= '<input type="date" id="' . $id . '" name="' . $name . '" class="' . $class . '" value="' . $value . '" ' . $misc . '/>';
					if ( ! empty( $label ) ) {
						$output .= '</p>';
					}
					break;
				case 'time':
					if ( ! empty( $label ) ) {
						$output .= '<p><label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label>';
					}
					$output .= '<input type="time" id="' . $id . '" name="' . $name . '" class="' . $class . '" value="' . $value . '" ' . $misc . '/>';
					if ( ! empty( $label ) ) {
						$output .= '</p>';
					}
					break;
				case 'tel':
					if ( ! empty( $label ) ) {
						$output .= '<p><label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label>';
					}
					$output .= '<input type="tel" id="' . $id . '" name="' . $name . '" class="' . $class . '" value="' . $value . '" ' . $misc . '/>';
					if ( ! empty( $label ) ) {
						$output .= '</p>';
					}
					break;
				case 'hidden':
					$output .= '<input type="hidden" id="' . $id . '" name="' . $name . '" value="' . $value . '" ' . $misc . '/>';
					break;
				case 'select':
					if ( ! empty( $label ) ) {
						$output .= '<p><label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label>';
					}
					$output .= '<select id="' . $id . '" name="' . $name . '" class="' . $class . '" >';
					foreach ( $options as $option ) {
						$output .= '<option value="' . $option['value'] . '" ' . ( $option['value'] == $value ? 'selected="selected"' : '' ) . ' ' . $misc . ' ' . ( isset( $option['misc'] ) ? $option['misc'] : '' ) . '>' . $option['title'] . '</option>';
					}
					$output .= '</select></p>';
					break;
				case 'checkbox':
					$output .= '<p><input type="checkbox" id="' . $id . '" name="' . $name . '" class="' . $class . '"  value="' . $value . '" ' . $misc . '/>';
					if ( ! empty( $label ) ) {
						$output .= '<label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label>';
					}
					$output .= '</p>';
					break;
				case 'radio':
					$output .= '<input type="radio" id="' . $id . '" name="' . $name . '" class="' . $class . '" value="' . $value . '" ' . $misc . '/>';
					if ( ! empty( $label ) ) {
						$output .= ' <label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label>';
					}
					break;
				case 'textarea':
					if ( ! empty( $label ) ) {
						$output .= '<p><label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label>';
					}
					$output .= '<textarea id="' . $id . '" name="' . $name . '" class="' . $class . '" ' . $misc . '>' . $value . '</textarea>';
					if ( ! empty( $label ) ) {
						$output .= '</p>';
					}
					break;
				case 'wpeditor':
					ob_start();
					if ( ! empty( $label ) ) {
						echo '<p><label class="' . $type . '" for="' . $id . '">' . apply_filters( 'fes_' . $name . '_label', $label ) . '</label></p>';
					}
					wp_editor(
						html_entity_decode( $value ),
						$id,
						array(
							'editor_class'  => $class,
							'textarea_name' => $name,
							'media_buttons' => 1,
							'textarea_rows' => 6,
						)
					);
					/*if (!empty($label)) {
						echo '</p>';
					}*/
					$output .= ob_get_contents();
					ob_end_clean();
					break;
				case 'submit':
					$output .= '<p><input type="submit" id="' . $id . '" name="' . $name . '" class="' . $class . '" value="' . $value . '"/>';
					if ( ! empty( $label ) ) {
						$output .= '</p>';
					}
					break;
			}
			$output .= '</div>';
			if ( isset( $field['after'] ) ) {
				$output .= $field['after'];
			}
			ob_start();
			do_action( 'fes_' . $name . '_after', $post_id );
			$output .= ob_get_contents();
			ob_end_clean();
		}
		$output .= '</div>';
		return $output;
	}
}

