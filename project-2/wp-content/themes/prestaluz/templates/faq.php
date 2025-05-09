<div class="faq">
    <div class="heading">
        <?php  the_field('faq_title'); ?>
    </div>
    <div class="accordion">

        <?php

            $faqContent = get_field('faq_list');

            if (is_array($faqContent) && !empty($faqContent)) {
                foreach ($faqContent as $faq) :
                    ?>
                        <div class="accordion_card">
                            <div class="accordion_header">
                                <div class="icon">
                                    <div class="img"></div>
                                </div>
                                <p><?php echo $faq['question']?></p>
                            </div>
                            <div class="accordion_body">
                                <div class="content">
                                    <?php echo $faq['answer']?>
                                </div>
                            </div>
                        </div>
                    <?php
                endforeach;
            } else {
                echo 'No faq found';
            }

        ?>

    </div>
</div>