<? 
$slider = get_field('page-settings-slider');
$breadcrumb = get_field('page-settings-breadcrumb');
$sidebar = get_field('page-settings-sidebar');
$image = get_the_post_thumbnail_url($post, '1536x1536');
if ($image && (!$slider || is_array($slider) && count($slider) == 0))
	$slider = [
		[
			'sizes' => [
				'1536x1536' => $image
			]
		]
	];

get_header(); ?>

<main>
	<? slider_render($slider);

	if ($breadcrumb)
  	fcc_title_bar_render(false, false, false, $post->ID);

	if ($sidebar) { ?>
		<div class="flex-grid">
			<div class="col-8">
				<?= the_content(); ?>
			</div>
			<div class="col-1"></div>
			<div class="col-3 sidebar">
				<?= do_shortcode($sidebar); ?>
				<? dynamic_sidebar('sidebar-extras'); ?>
			</div>
		</div>
	<? } else {
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post(); ?>
				<? the_content(); ?>
			<? endwhile;
		else :
			get_template_part( 'template-parts/content', 'none' );
		endif;
	} ?>
</main><!-- #main -->

<? get_footer();
