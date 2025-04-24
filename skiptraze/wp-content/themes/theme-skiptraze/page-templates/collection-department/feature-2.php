<div class="features-2">
    <div class="content-wrapper">
        <div class="div-5">
            <div class="text-3">
                <div class="heading">
                    <?php the_field('cd_feature_heading');?>
                </div>
                <div class="title">
                    <?php the_field('cd_feature_title');?>
                </div>
                <div class="frame-5">
                    <?php
                        $cd_feature_list = get_field('cd_feature_list');
                        $cd_feature_list_count = count($cd_feature_list);
                        $cd_feature_list_display = 0;

                        if ($cd_feature_list_count > 0) {
                            while ($cd_feature_list_display < $cd_feature_list_count) {
                                echo '<div class="checklist">';

                                for ($i = 0; $i < 6 && $cd_feature_list_display < $cd_feature_list_count; $i++) {
                                    echo '<div class="check-items">';
                                    echo '<img src="' . get_field('cd_feature_icon') . '" alt="check icon" class="check-icon">';
                                    echo '<p class="text-4">' . $cd_feature_list[$cd_feature_list_display]['cd_feature_item'] . '</p>';
                                    echo '</div>';
                                    $cd_feature_list_display++;
                                }

                                echo '</div>';
                            }
                        }
                    ?>
                </div>
                <div class="divider">
                    <div class="rectangle"></div>
                </div>
                
                <a href="<?php the_field('collection_button_link2');?>" class="button-2">
                    <span class="label-4"><?php the_field('collection_button_label2');?></span>
                </a>
            </div>
        </div>
    </div>
</div>