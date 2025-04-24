<div class="faq contact">
    <div class="container">
        <?php get_template_part('page-templates/page-components/faq-heading'); ?>
        <div class="filter-faqs">
            <ul class="accord-list">
                <li class="accord-item collect-accord active"><?php the_field('faq_collection_departments_label','options');?></li>
                <li class="accord-item data-accord"><?php the_field('faq_data_partners_label','options');?></li>
            </ul>

            <?php get_template_part('page-templates/page-components/collection-accordion'); ?>
            <?php get_template_part('page-templates/page-components/data-accordion'); ?>
            
        </div>
        
    </div>
</div>