<div class="document_page">
    <div class="document_container">
        <div class="document_heading">
            <div class="heading_text"><?php the_field('document_heading');?></div>
        </div>
        <div class="document_wrapper">
            <div class="document_list">
                <div class="table_heading">
                    <div class="description"><?php the_field('table_description');?></div>
                    <div class="document"><?php the_field('table_document');?></div>
                </div>
                <div class="table_list">
                    <?php
                        if( have_rows('document_list') ):
                            while( have_rows('document_list') ) : the_row();
                                $document_file_name = get_sub_field('document_file');
                                ?>
                                    <div class="list_item">
                                        <div class="file_desc"><?php the_sub_field('document_title');?></div>
                                        <div class="file">
                                            <a 
                                                href="<?php echo esc_url($document_file_name); ?>"
                                                target="_blank">
                                                <img src="<?php the_field('file_icon');?>" alt="">
                                                <?php echo basename($document_file_name); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php
                            endwhile;
                        endif;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>