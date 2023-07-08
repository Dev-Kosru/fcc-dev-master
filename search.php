<?php get_header(); ?>

<main class="page-search">
  <div class="fcc-title-bar">
    <div class="fcc-title-bar-title">
      <span>Search results</span>
    </div>
  </div>

  <div class="flex-grid">
    <div class="col-8">
      <div id="content" class="has-content">
        <?php
        if ( have_posts() ) :

          /* Start the Loop */
          while ( have_posts() ) :
            global $post;
            the_post(); 
            
            $thumb = get_the_post_thumbnail_url(null, 'medium_large'); ?>

            <div class="fcc-preview">
              <a href="<?= get_permalink($item) ?>" class="fcc-preview-image">
                <img src='<?= $thumb ?: get_stylesheet_directory_uri() . '/images/logo-blue.png' ?>' />
              </a>
              <div class="fcc-preview-content">
                <div class="fcc-preview-title"><a href="<?= get_the_permalink() ?>"><?= get_the_title() ?></a></div>
                <div class="fcc-preview-date"><?= get_post_type() == 'event' ? fcc_event_date($post) : get_the_date('') ?></div>
                <div class="fcc-preview-desc"><?= mb_strimwidth(trim(strip_tags(do_shortcode(get_the_content()))), 0, 300, '...') ?></div>
                <div class="fcc-preview-actions">
                  <a class="btn btn-yellow" href="<?= get_the_permalink() ?>">Read More</a>
                  <a class="fcc-share" href="<?= get_the_permalink() ?>" title="<?= get_the_title() ?>"><i class="fa fa-share-alt"></i></a>
                </div>
              </div>
            </div>

          <?php endwhile;

          the_posts_pagination();

        else :

          get_template_part( 'template-parts/content', 'none' );

        endif; ?>
      </div>
    </div>
    <div class="col-1"></div>
    <div class="col-3 sidebar">
      <?php dynamic_sidebar('sidebar-extras'); ?>
    </div>

	</main><!-- #main -->

<?php
get_footer();
