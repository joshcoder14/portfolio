<div class="features-2">
    <div class="content-wrapper">
        <div class="div-5">
            <div class="text-3">
                <div class="heading">
                    <?php the_field('dp_feature_heading');?>
                </div>
                <div class="title">
                    <?php the_field('dp_feature_title');?>
                </div>
                <div class="frame-5">
                    <?php
                        $dp_feature_list = get_field('dp_feature_list');
                        $dp_feature_list_count = count($dp_feature_list);
                        $dp_feature_list_display = 0;

                        if ($dp_feature_list_count > 0) {
                            while ($dp_feature_list_display < $dp_feature_list_count) {
                                echo '<div class="checklist">';

                                for ($i = 0; $i < 5 && $dp_feature_list_display < $dp_feature_list_count; $i++) {
                                    echo '<div class="check-items">';
                                    echo '<img src="' . get_field('dp_feature_list_icon') . '" alt="check icon" class="check-icon">';
                                    echo '<p class="text-4">' . $dp_feature_list[$dp_feature_list_display]['dp_feature_item'] . '</p>';
                                    echo '</div>';
                                    $dp_feature_list_display++;
                                }

                                echo '</div>';
                            }
                        }
                    ?>
                </div>
                <div class="divider">
                    <div class="rectangle"></div>
                </div>
                
                <a href="mailto:<?php the_field('data_button','options');?>" class="button-2">
                    <span class="label-4"><?php the_field('data_button_label','options');?></span>
                </a>
            </div>
        </div>
    </div>
</div>