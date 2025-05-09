<?php if ( function_exists( 'pll_the_languages' ) ) : ?>
    <div class="languages dropdown">
        <?php
            
            // Gets the pll_the_languages() raw code
            $languages = pll_the_languages(array(
                'display_names_as'       => 'slug',
                'hide_if_no_translation' => 0,
                'raw'                    => true
            ));

            $output = '';
            
            // Checks if the $languages is not empty
            if (!empty($languages)) {

                // Creates the $output variable with languages container
                $output = '<ul class="dropdown_content" aria-labelledby="language-dropdown">';
                
                // Runs the loop through all languages
                foreach ($languages as $language) {

                    // Variables containing language data
                    $id             = $language['id'];
                    $flag           = $language['flag'];
                    $slug           = $language['slug'];
                    $url            = $language['url'];
                    $current        = $language['current_lang'] ? ' languages__item--current' : '';
                    $no_translation = $language['no_translation'];

                    // Checks if the page has translation in this language
                    if (!$no_translation) {

                        // Check if it's current language
                        if ( $current ) {
                            $current_flag_url = $flag;
                            $output .= sprintf('
                            <li class="list current-lang">
                                <a href="#!">
                                    <img src="%s" class="flag-icon" alt="language">
                                    %s
                                </a>
                            </li>',$flag, $slug);
                        } else {
                            $output .= sprintf('
                            <li class="list">
                                <a href="%s">
                                    <img src="%s" class="flag-icon" alt="language">
                                    %s
                                </a>
                            </li>', $url,$flag, $slug);
                        }
                        
                    }
                }

                $output .= '</ul>';
            }

            echo sprintf(
                '<button id="btn_dropdown" class="btn_dropdown">
                    <img src="%s" class="flag-icon" alt="language">
                    <span class="current-language">%s</span>
                </button>', 
                $current_flag_url,
                pll_current_language() 
            );
            echo $output;
        ?>   
    </div>
<?php endif ?>