<?php
    add_action( 'acf/include_fields', function() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key' => 'group_651d011bac254',
            'title' => 'Mision and Vision Feature',
            'fields' => array(
                array(
                    'key' => 'field_651d011b08b23',
                    'label' => 'Feature',
                    'name' => 'feature',
                    'aria-label' => '',
                    'type' => 'repeater',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'layout' => 'table',
                    'pagination' => 0,
                    'min' => 0,
                    'max' => 0,
                    'collapsed' => '',
                    'button_label' => 'Add Row',
                    'rows_per_page' => 20,
                    'sub_fields' => array(
                        array(
                            'key' => 'field_651d0152a165f',
                            'label' => 'Icon',
                            'name' => 'icon',
                            'aria-label' => '',
                            'type' => 'image',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'return_format' => 'url',
                            'library' => 'all',
                            'min_width' => '',
                            'min_height' => '',
                            'min_size' => '',
                            'max_width' => '',
                            'max_height' => '',
                            'max_size' => '',
                            'mime_types' => '',
                            'preview_size' => 'medium',
                            'parent_repeater' => 'field_651d011b08b23',
                        ),
                        array(
                            'key' => 'field_651d0186a1660',
                            'label' => 'Title',
                            'name' => 'title',
                            'aria-label' => '',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'maxlength' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'parent_repeater' => 'field_651d011b08b23',
                        ),
                        array(
                            'key' => 'field_651d019da1661',
                            'label' => 'Sub-text',
                            'name' => 'sub-text',
                            'aria-label' => '',
                            'type' => 'textarea',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'default_value' => '',
                            'maxlength' => '',
                            'rows' => '',
                            'placeholder' => '',
                            'new_lines' => '',
                            'parent_repeater' => 'field_651d011b08b23',
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'acf-options-options',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
            'show_in_rest' => 0,
        ) );
    } );
?>