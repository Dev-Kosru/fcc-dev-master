<?php
get_header();
?>

	<main>

		<?php
		if ( have_posts() ) :

			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				if (get_post_type() !== 'dining_request' && 
					get_post_type() !== 'private_request' &&
					get_post_type() !== 'partner-club' &&
					get_post_type() !== 'board-member'): ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class('container'); ?>>
						<div class="entry-header">
							<?php
								the_title( '<h2 class="entry-title">', '</h2>' );
							?>
						</div><!-- .entry-header -->

						<div class="entry-content">
							<?php the_content(); ?>
						</div><!-- .entry-content -->

					</article><!-- #post-<?php the_ID(); ?> -->
				<? endif; ?>
		
			<?php endwhile;

			the_posts_navigation();

		else :

			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>

	</main><!-- #main -->

<?php
get_footer();
