<?php
    add_action( 'acf/include_fields', function() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key' => 'group_651cf4c33f7b5',
            'title' => 'Homepage',
            'fields' => array(
                array(
                    'key' => 'field_651cf4c326f87',
                    'label' => 'Collection Department Section',
                    'name' => '',
                    'aria-label' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_651cf4dc26f88',
                    'label' => 'Collection supporting Text',
                    'name' => 'collection_supporting_text',
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
                    'default_value' => 'COLLECTION DEPARTMENTS',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_651cf52a26f89',
                    'label' => 'Collection Heading Text',
                    'name' => 'collection_heading_text',
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
                    'default_value' => 'Fresh contact details for non-contactable debtors',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_651cf55026f8a',
                    'label' => 'Collection Sub text',
                    'name' => 'collection_sub_text',
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
                    'default_value' => 'We connect you with sales databases in real-time to find fresh phone numbers, emails and addresses of debtors you want to collect from',
                    'maxlength' => '',
                    'rows' => '',
                    'placeholder' => '',
                    'new_lines' => '',
                ),
                array(
                    'key' => 'field_rZV1dh',
                    'label' => 'Collection Button Label',
                    'name' => 'collection_button_label_homepage',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 'Get fresh data',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '', 
                ),
                array(
                    'key' => 'field_652ce0fbdf046',
                    'label' => 'Collection Button Link',
                    'name' => 'collection_button_link',
                    'aria-label' => '',
                    'type' => 'page_link',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'post_type' => '',
                    'post_status' => '',
                    'taxonomy' => '',
                    'allow_archives' => 1,
                    'multiple' => 0,
                    'allow_null' => 0,
                ),
                array(
                    'key' => 'field_651d0c7828c85',
                    'label' => 'Data Department Section',
                    'name' => '',
                    'aria-label' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_651d0cae28c86',
                    'label' => 'Data supporting Text',
                    'name' => 'data_supporting_text',
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
                    'default_value' => 'DATA PARTNERS',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_651d0cc528c87',
                    'label' => 'Data Heading Text',
                    'name' => 'Data_heading_text',
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
                    'default_value' => 'Monetise your sales customer database',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_651d0d1928c88',
                    'label' => 'Data Sub text',
                    'name' => 'data_sub_text',
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
                    'default_value' => 'We connect you with collection departments looking for fresh phone numbers, emails and addresses of debtors',
                    'maxlength' => '',
                    'rows' => '',
                    'placeholder' => '',
                    'new_lines' => '',
                ),
                array(
                    'key' => 'field_652ce126aa9dc',
                    'label' => 'Data Button Link',
                    'name' => 'data_button_link',
                    'aria-label' => '',
                    'type' => 'page_link',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'post_type' => '',
                    'post_status' => '',
                    'taxonomy' => '',
                    'allow_archives' => 1,
                    'multiple' => 0,
                    'allow_null' => 0,
                ),
                array(
                    'key' => 'field_651d101ebaa83',
                    'label' => 'About Us Section',
                    'name' => '',
                    'aria-label' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_651d1031baa84',
                    'label' => 'Device Image',
                    'name' => 'device_image',
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
                ),
                array(
                    'key' => 'field_651d1052baa85',
                    'label' => 'About section header',
                    'name' => 'about_section_header',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 'ABOUT US',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_651d108ebaa86',
                    'label' => 'About Section Title',
                    'name' => 'about_section_title',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 'We are SkipTraze',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_651d10a1baa87',
                    'label' => 'About section subtext',
                    'name' => 'about_section_subtext',
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
                    'default_value' => 'SkipTraze is a groundbreaking international skip tracing platform. We connect debt collectors with fresh, real-time data to make their efforts more effective. We aim to be the go-to solution for the debt collection industry, transforming the landscape by offering real-time, consumer-consented contact information.',
                    'maxlength' => '',
                    'rows' => '',
                    'placeholder' => '',
                    'new_lines' => '',
                ),
                array(
                    'key' => 'field_651d2ebd4e697',
                    'label' => 'Read More Button Label',
                    'name' => 'read_more_label',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 'Read more',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_652ce137aa9dd',
                    'label' => 'Read More Link',
                    'name' => 'read_more_link',
                    'aria-label' => '',
                    'type' => 'page_link',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'post_type' => '',
                    'post_status' => '',
                    'taxonomy' => '',
                    'allow_archives' => 1,
                    'multiple' => 0,
                    'allow_null' => 0,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'page_template',
                        'operator' => '==',
                        'value' => 'template-home.php',
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