<div class="logo-clouds">
    <div class="container-6">
        <p class="heading-3">
            <?php the_field('client_section_title','options');?>
        </p>
        <div class="company-logos swiper">
            <div class="slider-content">
                <div class="row swiper-wrapper">
                    <?php
                        $icons = get_field('client_logo', 'options');
                        $icon_count = count($icons); // Get the total number of icons
                        $icons_displayed = 0; // Initialize a counter for displayed icons
                        
                        // Check if there are icons to display
                        if ($icon_count > 0) {
                            // Start a loop to display icons
                            while ($icons_displayed < $icon_count) {
                                echo '<div class="media-company-wrapper swiper-slide">';
                                // Loop to display 2 icons or fewer if not enough left
                                for ($i = 0; $i < 2 && $icons_displayed < $icon_count; $i++) {
                                    echo '<div class="client-wrapper">';
                                    echo '<img src="' . $icons[$icons_displayed]['icon'] . '" alt="client icon" class="icon">'; // Display the icon
                                    echo '</div>';
                                    $icons_displayed++; // Increment the displayed icon counter
                                }
                                echo '</div>';
                            }
                        }
                    ?>
                </div>
            </div>
            <div class="space"></div>
            <div class="client-pagination"></div>
        </div>
    </div>
</div>