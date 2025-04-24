<?php
/*
Template Name: Block (Home child)
*/
?>
<?php get_header();?>

<main class="main">

	<?php
		// section 1
		get_template_part('templates/first','section');
		// section 2
		get_template_part('templates/second','section');
		// section 3
		get_template_part('templates/third','section');
		// section 4
		get_template_part('templates/fourth','section');
		// section 5
		get_template_part('templates/fifth','section');
		// section 6
		get_template_part('templates/sixth','section');

	?>
	

</main>



<?php get_footer();?>