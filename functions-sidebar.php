<?
add_shortcode('news', function() { 
  ob_start(); ?>
  <div class="sidebar-news">
    <div class="title-dark">News</div>
    <?php
    $news = get_posts([
      'category_name' => 'news',
      'posts_per_page' => 4,
    ]);
    foreach ($news as $item) { ?>
      <div class="fcc-preview">
        <a href="<?= get_permalink($item) ?>" class="fcc-preview-image">
          <img src="<?= get_the_post_thumbnail_url($item, 'medium_large') ?>" />
        </a>
        <div class="fcc-preview-content">
          <div class="fcc-preview-title"><a href="<?= get_permalink($item) ?>"><?= $item->post_title ?></a></div>
          <div class="fcc-preview-actions">
            <a class="btn btn-yellow" href="<?= get_permalink($item) ?>">Read More</a>
            <a class="fcc-share" href="<?= get_permalink($item) ?>" title="<?= $item->post_title ?>"><i class="fa fa-share-alt"></i></a>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
<? return ob_get_clean();
});

add_shortcode('speakers', function() { 
  ob_start(); ?>
  <div class="sidebar-latest-speakers">
    <div class="title-dark">Latest Speakers</div>
    <?php
    $speakers = get_posts([
      'post_type' => 'event',
      'meta_query' => [
        'relation' => 'AND',
        'end' => [
          'key' => '_event_end_local',
          'value' => date("Y-m-d 00:00:00"),
          'compare' => '<=', 
        ],
      ],
      'orderby' => [
        'end' => 'DESC'
      ],
      'tax_query' => [
        [
          'taxonomy' => 'event-categories',
          'terms' => 'speakers',
          'field' => 'slug',
        ]
      ],
      'posts_per_page' => 1
    ]);
    foreach ($speakers as $item) { ?>
      <div class="fcc-preview">
        <a href="<?= get_permalink($item) ?>" class="fcc-preview-image">
          <img src="<?= get_the_post_thumbnail_url($item, 'medium_large') ?>" />
        </a>
        <div class="fcc-preview-content">
          <div class="fcc-preview-title"><a href="<?= get_permalink($item) ?>"><?= $item->post_title ?></a></div>
          <div class="fcc-preview-date"><?= get_field('date', $item->ID) ?></div>
          <div class="fcc-preview-desc"><?= mb_strimwidth(trim(strip_tags(do_shortcode($item->post_content))), 0, 200, "...") ?></div>
          <div class="fcc-preview-actions">
            <a class="btn btn-yellow" href="<?= get_permalink($item) ?>">Read More</a>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
<? return ob_get_clean();
});

add_shortcode('hrpa', function() { 
  ob_start(); ?>
  <div class="sidebar-hrpa">
  </div>
<? return ob_get_clean();
});

add_shortcode('dining_reservation', function() { 
  ob_start(); ?>
  <div class="widget">
    <a href="/members-area/#/app/dining"><img src="<?= get_stylesheet_directory_uri() ?>/images/dining-reservation.jpg"></a>
  </div>
<? return ob_get_clean();
});

add_shortcode('sports_schedule', function() { 
  $sports = get_posts([
    'post_type' => 'event',
    'post_status' => 'publish',
    'posts_per_page' => 1,
    'tax_query' => [
      [
        'taxonomy' => 'event-categories',
        'terms' => 'sports-schedule',
        'field' => 'slug',
      ]
    ],
  ]);
  ob_start(); ?>
  <div class="widget">
    <a href="/events?category=sports-schedule"><div class="title-dark">Sports Schedule</div></a>
    <a href="<?= get_the_permalink($sports[0]->ID) ?>"><img src="<?= get_the_post_thumbnail_url($sports[0]->ID, 'thumb') ?>"></a>
  </div>
<? return ob_get_clean();
});