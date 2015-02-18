<?php /*
Template Name: General GA setup
*/ ?>
<?php 
$themePath = get_stylesheet_directory_uri(); // we want the child theme url
$code = get_gauntlet_attr("code");
?>
<?php get_header(); ?>
<?php get_template_part('parts/globals/instructions'); ?>
<main>
	<section class="row side-right gutter pad-bottom tall">
		<div class="column one">
			<?php get_template_part('parts/headers/'.$code); ?>
			<?php get_template_part('parts/descriptions/'.$code); ?>
			<?php get_template_part('parts/test_blocks/general_links'); ?>
			<?php get_template_part('parts/content/'.$code); ?>
			<?php get_template_part('parts/test_blocks/gerneral_social_links'); ?>
			
			
			<?php get_template_part('parts/test_blocks/extended_links'); ?>
		</div><!--/column-->
		<div class="column two">
			<?php get_template_part('parts/sidebars/'.$code); ?>
		</div><!--/column-->
	</section>

</main>

<?php get_footer(); ?>