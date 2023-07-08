<?
$term_id = get_queried_object_id();
$term = get_term($term_id);

get_header() ?>

<main class="page-event-category">
  <div class="fcc-title-bar">
    <div class="fcc-title-bar-title">
      <span><a href="/events">Events</a></span>
      <span><?= $term->name ?></span>
    </div>

  </div>

  <div class="flex-grid">
    <div class="col-8">
      <div class='title-dark'><?= $term->name ?></div>
      
      <?= nl2br($term->description) ?>

      <div id="content">
        <? while(have_posts()) { 
          the_post(); ?>
          <div class="fcc-preview">
            <a href="<?= get_permalink($post) ?>" class="fcc-preview-image">
              <img src="<?= get_the_post_thumbnail_url($post, 'medium_large') ?>" />
            </a>
            <div class="fcc-preview-content">
              <div class="fcc-preview-title"><a href="<?= get_permalink($post) ?>"><?= $post->post_title ?></a></div>
              <div class="fcc-preview-date"><?= fcc_event_date($post) ?></div>
              <div class="fcc-preview-desc"><?= mb_strimwidth(trim(html_entity_decode(strip_tags(do_shortcode($post->post_content))), " \t\n\r\0\x0B\xC2\xA0"), 0, 300, '...') ?></div>
              <div class="fcc-preview-actions">
                <a class="btn btn-yellow" href="<?= get_permalink($post) ?>">Read More</a>
                <!-- <a class="btn btn-blue">Watch Video</a> -->
              </div>
            </div>
          </div>
        <? } ?>
      </div>

      <? the_posts_pagination(); ?>
    </div>
    <div class="col-1"></div>
    <div class="col-3 sidebar">
      <?php dynamic_sidebar('sidebar-extras'); ?>
    </div>
  </div>
</main>

<?php get_footer(); ?>