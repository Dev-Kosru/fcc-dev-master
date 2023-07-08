<?php

function fcc_carousel_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'Carousel'; return;
  }

  $posts = get_field('posts'); ?>

  <div class="fcc-carousel carousel large-dots alignfull fadeIn" data-flickity='{ "lazyLoad": 1, "cellAlign": "center", "contain": true, "adaptiveHeight": false, "autoPlay": 5000, "wrapAround": true, "imagesLoaded": true, "prevNextButtons": false, "fade": true, "pauseAutoPlayOnHover": false }'> 
    <div class="carousel-cell">
      <div class="cell-inner">
        <img class="fullwidth" src="<?= get_stylesheet_directory_uri() ?>/images/FCC-Exterior.jpg" />
      </div>
    </div>
    <?php foreach ($posts as $post) { ?>
      <div class="carousel-cell">
        <div class="cell-inner">
          <img src="<?= get_the_post_thumbnail_url($post, 'medium_large') ?>" />
          <div class="cell-content">
            <a href="<?= get_permalink($post) ?>" class="cell-subtitle"><?= $post->post_title ?></a>
            <a class="cell-readmore" href="<?= get_permalink($post) ?>">Read More</a>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
<?php
}

function fcc_notices_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'Notices'; return;
  }
  $notices = get_posts([
    'category_name' => 'club-notices',
    'posts_per_page' => 3
  ]); 
  $notices_chunks = array_chunk($notices, 3); ?>

  <div class="fcc-notices fadeIn">
    <div class="title-dark">Notices</div>

    <div class="carousel" data-flickity='{"pageDots": <?= count($notices_chunks) > 1 ? 'true' : 'false' ?>, "contain": true, "adaptiveHeight": false, "autoPlay": 3500, "wrapAround": true, "prevNextButtons": false }'>
      <?php foreach ($notices_chunks as $notices_chunk) { ?>
        <div class="carousel-cell">
          <?php foreach ($notices_chunk as $notice) { ?>
            <div class="fcc-notices-item">
              <i class="disc-dark"></i>
              <div class="content"><?= $notice->post_title ?> <a class="read-more" href="<?= get_permalink($notice) ?>">Read More...</a></div>
            </div>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
  </div>
<?php
}

function fcc_stream_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'Video'; return;
  }

  $image = get_field('image');
  $link = get_field('link'); 
  if (strpos($link, '/watch') !== false) 
    $link = str_replace('watch?v=', 'embed/', $link); ?>

  <div class="fcc-stream fadeIn">
    <div class="title-light">FCC Video</div>
    <a class="fcc-stream-image" href="<?= $link ?>" style="background-image: url(<?= $image['sizes']['medium_large'] ?>)"></a>
  </div>
<?php
}

function fcc_twitter_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'Twitter'; return;
  } ?>
  <div class="fcc-twitter fadeIn">
    <div class="title-light">Twitter Updates</div>
    <div class="fcc-twitter-embed">
      <a class="twitter-timeline" data-height="420" href="https://twitter.com/fcchk?ref_src=twsrc%5Etfw">Tweets by fcchk</a>
      <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
    </div>
  </div>
<?php
}

add_shortcode('twitter', function( $atts ) {
  ob_start();
  fcc_twitter_render(false, false, false);
  return ob_get_clean();
});

function fcc_press_freedom_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'Press Freedom'; return;
  }
  $posts = get_posts([
    'category_name' => 'press-freedom',
    'posts_per_page' => 6
  ]); 
  $posts = array_chunk($posts, 3); ?>

  <div class="fcc-press_freedom fadeIn">
    <div class="title-dark">Press Freedom</div>

    <div class="carousel" data-flickity='{ "contain": true, "adaptiveHeight": false, "autoPlay": 4500, "wrapAround": true }'>
      <?php foreach ($posts as $posts_chunk) { ?>
        <div class="carousel-cell">
          <?php foreach ($posts_chunk as $post) { ?>
            <div class="fcc-press_freedom-item">
              <i class="fa fa-pen-fancy"></i>
              <div class="content"><?= $post->post_title ?> <a class="read-more" href="<?= get_permalink($post) ?>">Read More...</a></div>
            </div>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
  </div>
<?php
}

function fcc_events_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'Events'; return;
  }
  $events = get_posts([
    'post_type' => 'event',
    'meta_query' => [
      'relation' => 'AND',
      'start' => [
        'key' => '_event_start_local',
        'value' => "9999-99-99 99:99:99",
        'compare' => '<=', 
      ],
      'end' => [
        'key' => '_event_end_local',
        'value' => date("Y-m-d 00:00:00"),
        'compare' => '>=', 
      ],
    ],
    'orderby' => [
      'start' => 'ASC'
    ],
    'tax_query' => [
      [
        'taxonomy' => 'event-categories',
        'terms' => get_field('categories'),
      ],
      [
        'taxonomy' => 'event-categories',
        'terms' => get_field('exclude_categories'),
        'operator' => 'NOT IN',
      ],
    ],
    'posts_per_page' => -1
  ]); ?>

  <div class="fcc-events fadeIn">
    <div class="title-dark"><?= get_field('title') ?></div>

    <div class="carousel" data-flickity='{ "contain": true, "cellAlign": "center", "adaptiveHeight": false, "autoPlay": <?= count($events) > 3 ? rand(3000, 5000) : 'false' ?>, "wrapAround": <?= count($events) > 3 ? 'true' : 'false' ?> }'>
      <?php foreach ($events as $event) { ?>
        <div class="carousel-cell fcc-preview">
          <a href="<?= get_permalink($event) ?>" class="fcc-preview-image">
            <img src="<?= get_the_post_thumbnail_url($event, 'medium_large') ?>" />
          </a>
          <div class="fcc-preview-content">
            <div class="fcc-preview-title"><a href="<?= get_permalink($event) ?>"><?= $event->post_title ?></a></div>
            <div class="fcc-preview-date"><?= fcc_event_date($event) ?></div>
            <div class="fcc-preview-actions">
              <a class="btn btn-yellow" href="<?= get_permalink($event) ?>">Read More</a>
              
              <? if (get_field('event_quota', $event->ID) > 0) {?>
                <a class="btn btn-blue" href="/members-area/#/app/events_event/<?= $event->ID ?>">Sign Up</a>
              <? } ?>
              
              <a class="fcc-share" href="<?= get_permalink($event) ?>" title="<?= $event->post_title ?>"><i class="fa fa-share-alt"></i></a>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
    
    <div class="clearfix"></div>
  </div>
<?php
}

function fcc_promotions_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'Promotions'; return;
  }

  $promotions = get_field('posts');
  if (!$promotions) return; ?>

  <div class="fcc-promotions fadeIn">
    <div class="title-dark">Promotions</div>

    <div class="carousel" data-flickity='{ "fade": true, "cellAlign": "center", "wrapAround": true, "adaptiveHeight": false, "autoPlay": 4200, "prevNextButtons": false }'>
      <?php foreach ($promotions as $promotion) { ?>
        <div class="carousel-cell fcc-preview">
          <a href="<?= get_permalink($promotion) ?>" class="fcc-preview-image">
            <img src="<?= get_the_post_thumbnail_url($promotion, 'medium_large') ?>" />
          </a>
          <div class="fcc-preview-content">
            <div class="fcc-preview-title"><a href="<?= get_permalink($promotion) ?>"><?= $promotion->post_title ?></a></div>
            <div class="fcc-preview-date"><?= fcc_event_date($promotion) ?></div>
            <div class="fcc-preview-desc"><?= mb_strimwidth(trim(strip_tags(do_shortcode($promotion->post_content))), 0, 100, '...') ?: '&nbsp;' ?></div>
            <div class="fcc-preview-actions">
              <a class="btn btn-white" href="<?= get_permalink($promotion) ?>">Read More</a>
              
              <? if (get_field('event_quota', $promotion->ID) > 0) {?><a class="btn btn-blue" href="/members-area/#/app/promotions_promotion/<?= $promotion->ID ?>">Sign Up</a><? } ?>
              
              <a class="fcc-share" href="<?= get_permalink($promotion) ?>" title="<?= $promotion->post_title ?>"><i class="fa fa-share-alt"></i></a>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>

    <div class="clearfix"></div>
  </div>
<?php
}

function fcc_speakers_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'Latest Speakers'; return;
  }
  $speakers = get_posts([
    'post_type' => 'event',
    'meta_query' => [
      'relation' => 'AND',
      'end' => [
        'key' => '_event_end_local',
        'value' => date("Y-m-d H:i:s"),
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
  ]); ?>

  <div class="fcc-speakers fadeIn">
    <div class="title-dark">Latest Speakers</div>

    <div class="carousel">
      <?php foreach ($speakers as $speaker) { ?>
        <div class="carousel-cell fcc-preview">
          <a href="<?= get_permalink($speaker) ?>" class="fcc-preview-image">
            <img src="<?= get_the_post_thumbnail_url($speaker, 'medium_large') ?>" />
          </a>
          <div class="fcc-preview-content">
            <div class="fcc-preview-title"><a href="<?= get_permalink($speaker) ?>"><?= $speaker->post_title ?></a></div>
            <div class="fcc-preview-date"><?= fcc_event_date($speaker) ?></div>
            <div class="fcc-preview-desc"><?= mb_strimwidth(trim(html_entity_decode(strip_tags(do_shortcode($speaker->post_content))), " \t\n\r\0\x0B\xC2\xA0"), 0, 300, '...') ?></div>
            <div class="fcc-preview-actions">
              <a class="btn btn-yellow" href="<?= get_permalink($speaker) ?>">Read More</a>
              <!-- <a class="btn btn-blue">Watch Video</a> -->
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
    <div class="clearfix"></div>
    <div class="aligncenter"><a href="/events/speaker-events/" class="btn btn-white">View All Speakers</a></div>
  </div>
<?php
}

function fcc_title_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'Title: ' . get_field('title'); return;
  }
  echo "<div class='" . (get_field('type') == 'dark' ? 'title-dark' : 'title-light') . " align{$block['align']}'>" . get_field('title') . "</div>";
}

function fcc_button_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'Button: ' . get_field('title'); return;
  }
  echo "<div class='fcc-button align{$block['align']}'><a href='" . get_field('link') . "' class='btn btn-" . get_field('type') . "' " . (get_field('new_tab') ? "target='_blank'" : "") . ">" . get_field('title') . "</a></div>";
}

function fcc_music_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'Music and Art'; return;
  }
  $musics = get_posts([
    'post_type' => 'event',
    'meta_query' => [
      'end' => [
        'key' => '_event_end_local',
        'value' => date("Y-m-d 00:00:00"),
        'compare' => '>=', 
      ],
    ],
    'orderby' => [
      'start' => 'ASC'
    ],
    'tax_query' => [
      [
        'taxonomy' => 'event-categories',
        'terms' => 'music',
        'field' => 'slug',
      ]
    ],
    // 'posts_per_page' => 6
    'posts_per_page' => 1
  ]); 
  if (!$musics)
    return; ?>

  <div class="fcc-music fadeIn">
    <div class="title-dark">Check out upcoming music and art performances at Berts</div>

    <div class="carousel" data-flickity='{ "cellAlign": "center", "wrapAround": true, "adaptiveHeight": false, "autoPlay": 4700, "prevNextButtons": false }'>
      <?php foreach ($musics as $music) { ?>
        <div class="carousel-cell fcc-preview">
          <a href="<?= get_permalink($music) ?>" class="fcc-preview-image">
            <img src="<?= get_the_post_thumbnail_url($music, 'medium_large') ?>" />
          </a>
          <div class="fcc-preview-content">
            <div class="fcc-preview-title"> <a href="<?= get_permalink($music) ?>"><?= $music->post_title ?></a></div>
            <div class="fcc-preview-date"><?= fcc_event_date($music) ?></div>
            <div class="fcc-preview-desc"><?= mb_strimwidth(trim(strip_tags(do_shortcode($music->post_content))), 0, 300, '...') ?></div>
            <div class="fcc-preview-actions">
              <a class="btn btn-yellow" href="<?= get_permalink($music) ?>">View Schedule</a>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>

    <div class="clearfix"></div>
  </div>
<?php
}

function fcc_icon_box_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'Icon Box'; return;
  } ?>

  <a href="<?= get_field('link') ?>" class="fcc-icon-box <?= get_field('shadow') ? 'line-shadow' : '' ?> align<?= $block['align'] ?> fadeIn">
    <div class="fcc-icon-box-icon">
      <img src='<?= get_field('icon')['sizes']['medium_large'] ?>' />
    </div>
    <? $content = get_field('content');
    if (!empty($content)) { ?>
      <div class="fcc-icon-box-content"><?= $content ?></div>
    <? } ?>
  </a>
<?php
}

function fcc_correspondent_preview_render($block, $content, $is_preview, $post_id, $wp_block, $mode = false) {
  if ($is_preview) {
    echo 'The Correspondent Preview'; return;
  }

  $mode = $mode ?: get_field('mode');

  switch ($mode) {
    case 'latest_story':
      $corr = get_posts([
        'post_type' => 'correspondent',
        'tax_query' => [
          [
            'taxonomy' => 'magazine-post-type',
            'terms' => 'online-ebook',
            'field' => 'slug',
            'operator' => 'NOT IN'
          ]
        ],
        'posts_per_page' => 1,
      ])[0];

      $issue = get_the_terms($corr, 'magazine-issue')[0];

      $ebook = get_posts([
        'post_type' => 'correspondent',
        'tax_query' => [
          [
            'taxonomy' => 'magazine-post-type',
            'terms' => 'online-ebook',
            'field' => 'slug',
          ],
          [
            'taxonomy' => 'magazine-issue',
            'terms' => $issue->term_id,
          ]
        ],
        'posts_per_page' => 1,
      ]); 
      $thumb = $ebook ? get_the_post_thumbnail_url($ebook[0], 'medium_large') : get_the_post_thumbnail_url($corr, 'medium_large'); ?>

      <div class="fcc-correspondent-preview fcc-correspondent-latest-story">
        <div class="title-dark">The Correspondent</div>
        <div class="fcc-correspondent-preview-columns">
          <a class="fcc-correspondent-preview-img" href="<?= get_permalink($corr) ?>"><img src="<?= $thumb ?: (get_stylesheet_directory_uri() . '/images/logo-blue.png') ?>" /></a>
          <div class="fcc-preview">
            <div class="fcc-preview-content">
              <div class="fcc-preview-title"><?= $corr->post_title ?></div>
              <div class="fcc-preview-date"><?= $corr->post_date ?></div>
              <div class="fcc-preview-desc"><?= mb_strimwidth(trim(strip_tags(do_shortcode($corr->post_content))), 0, 100, '...') ?></div>
              <div class="fcc-preview-actions">
                <a class="btn btn-yellow" href="<?= get_permalink($corr) ?>">Read More</a>
                <a class="fcc-share" href="<?= get_permalink($corr) ?>" title="<?= $corr->post_title ?>"><i class="fa fa-share-alt"></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    <? break; 
    case 'latest_issue': 
      $ebook = get_posts([
        'post_type' => 'correspondent',
        'tax_query' => [
          [
            'taxonomy' => 'magazine-post-type',
            'terms' => 'online-ebook',
            'field' => 'slug',
          ],
        ],
        'posts_per_page' => 1,
      ]); 
      $thumb = $ebook ? get_the_post_thumbnail_url($ebook[0], 'medium_large') : (get_stylesheet_directory_uri() . '/images/logo-blue.png'); ?>

      <div class="fcc-correspondent-preview fcc-correspondent-latest-issue">
        <img src="<?= $thumb ?>" />
        <a class="btn btn-yellow" target="_blank" href="<?= get_field('wpcf-issuu-link', $ebook[0]->ID) ?>">Read Magazine</a>
      </div>
      <? break; 
      
    case 'latest_stories':
      $posts = get_posts([
        'post_type' => 'correspondent',
        'posts_per_page' => 3
      ]); ?>

      <div class="fcc-correspondent-preview fcc-correspondent-latest-stories">
        <?php foreach ($posts as $post) { ?>
          <div class="fcc-correspondent-item">
            <i class="fa fa-scroll"></i>
            <div class="content"><?= $post->post_title ?> <a class="read-more" href="<?= get_permalink($post) ?>">Read More...</a></div>
          </div>
        <?php } ?>
      </div>
    <? break;
  }
}

add_shortcode('the_correspondent', function( $atts ) {
  ob_start();
  fcc_correspondent_preview_render(false, false, false, false, false);
  return ob_get_clean();
});

function fcc_gallery_preview_render($block, $content, $is_preview, $post_id, $wp_block, $id = null, $images = null, $thumbs = false, $fadeIn = true) {
  if ($is_preview) {
    echo 'Gallery'; return;
  } 
  
  $gallery_post = get_field('gallery');
  $gallery = $images ?: get_field('gallery_images', $id ?: $gallery_post->ID);
  if ($block !== false && count($gallery) < 10) { // basically for the "our moments" block
    $children = get_posts([
      'post_type' => 'gallery',
      'post_parent' => $id ?: $gallery_post->ID,
    ]);
    foreach ($children as $child) {
      if (count($gallery) > 10)
        break;

      $subGallery = get_field('gallery_images', $child->ID);
      if (is_array($subGallery))
        $gallery = array_merge($gallery, $subGallery);
    }
  }
  $thumbs = $thumbs ?: get_field('gallery_thumbs'); ?>

  <div class="fcc-gallery">
    <div class="carousel <?= $fadeIn ? 'fadeIn' : '' ?>" data-flickity='{ "lazyLoad": 1, "cellAlign": "center", "contain": true, "adaptiveHeight": true, "autoPlay": false, "imagesLoaded": true, "pageDots": false }'>
      <?php foreach ($gallery as $image) { ?>
        <div class="carousel-cell">
          <img src="<?= is_string($image) ? $image : $image['sizes']['medium_large'] ?>" />
        </div>
      <?php } ?>
    </div>

    <? if ($thumbs) { ?>
    <div class="gallery-thumbs <?= $fadeIn ? 'fadeIn' : '' ?>" data-flickity='{ "asNavFor": ".fcc-gallery .carousel", "lazyLoad": 1, "cellAlign": "center", "contain": true, "adaptiveHeight": false, "autoPlay": false, "imagesLoaded": true, "pageDots": false, "prevNextButtons": false }'>
      <?php foreach ($gallery as $image) { ?>
        <div class="carousel-cell">
          <img src="<?= is_string($image) ? $image : $image['sizes']['thumbnail'] ?>" />
        </div>
      <?php } ?>
    </div>
    <? } ?>
  </div>
<?php
}

add_shortcode('gallery', function( $atts ) {
  ob_start();
  fcc_gallery_preview_render(false, false, false, false, false, $atts['id']);
  return ob_get_clean();
});

function fcc_history_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'History'; return;
  } 
  $gallery = get_field('gallery'); ?>

  <div class="fcc-history">
    <div class="fcc-history-images carousel fadeIn" data-flickity='{ "lazyLoad": 1, "cellAlign": "center", "contain": true, "adaptiveHeight": false, "autoPlay": false, "imagesLoaded": true, "pageDots": false }'>
      <?php foreach ($gallery as $image) { ?>
        <div class="carousel-cell">
          <img src="<?= $image['sizes']['medium_large'] ?>" />
          <div class="img-desc"><?= $image['caption'] ?></div>
        </div>
      <?php } ?>
    </div>
    <div class="fcc-history-dates fadeIn" data-flickity='{ "asNavFor": ".fcc-history-images", "lazyLoad": 1, "cellAlign": "center", "contain": true, "adaptiveHeight": false, "autoPlay": false, "imagesLoaded": true, "pageDots": false, "prevNextButtons": false }'>
      <?php foreach ($gallery as $image) { ?>
        <div class="carousel-cell"><?= $image['title'] ?></div>
      <?php } ?>
    </div>
  </div>

<?php
}

function fcc_general_news_render($block, $content, $is_preview) {
  if ($is_preview) {
    echo 'General News'; return;
  }
  $news = get_posts([
    'category_name' => 'news',
    'posts_per_page' => 3
  ]); ?>

  <div class="fcc-general-news fadeIn">
    <div class="title-light">General News</div>

    <?php foreach ($news as $new) { ?>
      <div class="fcc-preview">
        <a href="<?= get_permalink($new) ?>" class="fcc-preview-image">
          <img src="<?= get_the_post_thumbnail_url($new, 'medium_large') ?>" />
        </a>
        <div class="fcc-preview-content">
          <div class="fcc-preview-title"><a href="<?= get_permalink($new) ?>"><?= $new->post_title ?></a></div>
          <div class="fcc-preview-date"><?= get_the_date('', $new) ?></div>
        </div>
        <div class="fcc-preview-desc"><?= mb_strimwidth(trim(strip_tags(do_shortcode($new->post_content))), 0, 300, '...') ?></div>
        <div class="fcc-preview-actions">
          <a class="btn btn-yellow" href="<?= get_permalink($new) ?>">Read More</a>
          <a class="fcc-share" href="<?= get_permalink($new) ?>" title="<?= $new->post_title ?>"><i class="fa fa-share-alt"></i></a>
        </div>  
      </div>
    <?php } ?>

    <div class="clearfix"></div>

    <div class="fcc-general-news-load">
      <a href="/news/?category=news" class="btn btn-white">Load More</a>
    </div>
  </div>
<?php
}

function fcc_title_bar_render($block = false, $content = false, $is_preview = false, $post_id = false, $wp_block = false, $root = false, $leaf = false) {
  global $post;

  if (isset($_GET['bare'])) return;

  if ($is_preview) {
    echo "Breadcrumb"; return;
  } ?>

  <div class="fcc-title-bar">
    <? $ancestors = get_post_ancestors($post); ?>
    
    <div class="fcc-title-bar-title">
      <?= $root ? "<span>$root</span>" : "" ?>

      <? foreach (array_reverse($ancestors) as $ancestor) { ?>
        <span><a href="<?= get_the_permalink($ancestor) ?>"><?= get_the_title($ancestor) ?></a></span>
      <? } ?>
        
      <span>
        <?= $leaf ? "<a href='" . get_the_permalink() . "'>" . get_the_title() . "</a>" : get_the_title() ?>
      </span>
      
      <?= $leaf ? "<span>$leaf</span>" : "" ?>
    </div>

  </div>
<?
}

function fcc_enquiry_box_render($block, $content, $is_preview, $post_id, $wp_block) {
  if ($is_preview) {
    echo "Enquiry Box"; return;
  } ?>

  <div class="fcc-enquiry-box">
    <div class="fcc-enquiry-box-btn">
      <i class="fas fa-info"></i>Enquiry Box<i class="fas fa-chevron-up"></i>
    </div>

    <div class="fcc-enquiry-box-form">
      <?= do_shortcode(get_field('form')) ?>
    </div>
  </div>
<?php 
}

function fcc_staff_render($block, $content, $is_preview, $post_id, $wp_block, $title = false, $link = false, $people = false) {
  if ($is_preview) {
    echo "Staff"; return;
  } 

  $title = $title ?: get_field('staff_title');
  $link = $link ?: get_field('staff_link'); 
  $people = $people ?: get_field('staff_people'); ?>

  <div class='fcc-staff'>
    <? if ($title) { ?>
      <div class="fcc-staff-title"><?= $title ?></div>
    <? } ?>

    <? if ($link) { ?>
      <a class="fcc-staff-link" href="<?= $link['url'] ?>"><?= $link['title'] ?></a>
    <? } else { ?>
      &nbsp;<br/>
    <? } ?>

    <div class="fcc-staff-people"> 
      <? foreach ($people as $person) { 
        $image = $person['image']['sizes']['medium_large']; ?>
        <div class="fcc-staff-person">
          <div class="fcc-staff-image-wrap">
            <div class="fcc-staff-image" <?= $image ? "style='background-image: url($image)'" : "" ?>></div>
          </div>
          <div class="fcc-staff-name"><?= $person['name'] ?></div>
          <div class="fcc-staff-position"><?= $person['position'] ?></div>
          <? if ($person['url']) { ?>
            <a class="fcc-staff-profile-link" href="<?= $person['url'] ?>">View Profile</a>
          <? } else { ?>
            <div>&nbsp;</div>
          <? } ?>
        </div>
      <? } ?>
    </div>
  </div>
<?php 
}

function fcc_product_categories_render($block, $content, $is_preview, $post_id, $wp_block, $title = false, $image = false, $parent = false) {
  if ($is_preview) {
    echo "Product Categories"; return;
  } 

  $title = $title ?: get_field('product-categories-title');
  $image = $image ?: get_field('product-categories-bg');
  $parent = $parent ?: get_field('product-categories-parent');
  $categories = (array) get_terms([
    'taxonomy' => 'product_cat',
    'parent' => $parent,
    'hide_empty' => false,
    'hierarchical' => false,
  ]); ?>

  <div class="fcc-product-categories" data-parallax="scroll" data-image-src="<?= $image ? $image['sizes']['1536x1536'] : '' ?>">
    <div class="title-light"><?= $title ?></div>
    
    <div class="carousel">
      <?php foreach ($categories as $category) { 
        if ($category->slug == 'uncategorized') continue;

        $thumbnail_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
        $image = wp_get_attachment_image_src( $thumbnail_id, 'woocommerce_thumbnail' )[0]; ?>

        <div class="carousel-cell">
          <a href="/members-area#/app/shop_category/product_cat&gt;<?= $category->slug ?>">
            <img src="<?= $image ?: get_stylesheet_directory_uri() . '/images/logo-blue.png' ?>" />
            <div class="title-light"><?= $category->name ?></div>
          </a>
        </div>
      <?php } ?>
    </div>
  </div>
<? }

function fcc_product_promotions_render($block, $content, $is_preview, $post_id, $wp_block, $cat = false, $promo = false) {
  if ($is_preview) {
    echo "Product Promotions"; return;
  } 

  $cat = $cat ?: get_field('product-promotions-cat'); 
  $promo = $promo ?: get_field('product-promotions-promo'); 
  $products = get_posts([
    'post_type' => 'product',
    'tax_query' => [
      $cat ? [
        'taxonomy' => 'product_cat',
        'terms' => $cat,
      ] : null,
      [
        'taxonomy' => 'product_promo',
        'terms' => $promo,
      ],
    ]
  ]); 
  if (empty($products)) return; ?>

  <div class="fcc-product-promotions">
    <div class="title-dark"><?= get_term($promo)->name ?></div>
    
    <div class="carousel" data-flickity='{ "contain": true, "cellAlign": "center", "adaptiveHeight": false, "autoPlay": false, "arrowShape": { "x0": 10, "x1": 60, "y1": 50, "x2": 60, "y2": 45, "x3": 15}, "wrapAround": true }'>
      <?php foreach ($products as $product) { 
        $thumbnail_id = get_post_thumbnail_id( $product->ID );
        $image = wp_get_attachment_image_src( $thumbnail_id, 'shop_single' )[0]; ?>

        <div class="carousel-cell">
          <div class="fcc-product">
            <a href="<?= get_the_permalink($product) ?>">
              <img src="<?= $image ?: get_stylesheet_directory_uri() . '/images/logo-blue.png' ?>" />
            </a>
            <div class="fcc-product-content">
              <div class="product-name"><?= $product->post_title ?></div>
              <!-- <div class="product-desc"><?= $product->post_content ?></div> -->
              <div class="product-btns">
                <a href="<?= get_the_permalink($product) ?>" class="btn btn-blue">View Product</a>
                <a href="/members-area/#/shop" class="btn btn-yellow">Add to Cart</a>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
<?
}

function fcc_product_slideshow_render($block, $content, $is_preview, $post_id) {
  if ($is_preview) {
    echo "Product Slideshow"; return;
  } 

  $products = get_field('product-slideshow-products'); ?>

  <div class="fcc-product-slideshow carousel" data-flickity='{ "cellAlign": "left", "wrapAround": true, "adaptiveHeight": false, "autoPlay": false, "arrowShape": { "x0": 10, "x1": 60, "y1": 50, "x2": 60, "y2": 45, "x3": 15} }'>
    <?php foreach ($products as $product) { 
      $thumbnail_id = get_post_thumbnail_id( $product->ID );
      $image = wp_get_attachment_image_src( $thumbnail_id, 'medium_large' )[0]; ?>

      <div class="carousel-cell" style="background-image: url(<?= $image ?>)">
        <div class="fcc-product-content">
          <div class="product-name"><?= $product->post_title ?></div>
          <a class="btn btn-yellow" href="<?= get_the_permalink($product) ?>"><?= $product->post_type == 'product' ? 'View Product' : 'Read More' ?></a>
        </div>
      </div>
    <?php } ?>
  </div>
<?
}

function fcc_timeline_render($block, $content, $is_preview, $post_id) {
  if ($is_preview) {
    echo "Timeline"; return;
  } ?>

  <div class="fcc-timeline">
    <? $items = get_field('items');
    foreach ($items as $item) { 
      $image = $item['image']['sizes']['medium_large']; ?>
      <div class="fcc-timeline-item">
        <div class="fcc-timeline-left">
          <div class="fcc-timeline-bubble">
            <div class="fcc-timeline-title"><?= $item['title'] ?></div>
            <div class="fcc-timeline-content">
              <?= $image ? "<img src='$image' />" : "" ?>
              <div class="fcc-timeline-description"><?= $item['description'] ?></div>
            </div>
          </div>
        </div>

        <div class="fcc-timeline-center">
          <div class="fcc-timeline-date"><?= $item['date'] ?></div>
        </div>

        <div class="fcc-timeline-right">
          <div class="fcc-timeline-bubble">
            <div class="fcc-timeline-title"><?= $item['title'] ?></div>
            <div class="fcc-timeline-content">
              <?= $image ? "<img src='$image' />" : "" ?>
              <div class="fcc-timeline-description"><?= $item['description'] ?></div>
            </div>
          </div>
        </div>
      </div>
    <? } ?>
  </div>
<? }

function fcc_accordion_render($block, $content, $is_preview, $post_id) {
  if ($is_preview) {
    echo "Accordion"; return;
  } ?>

  <div class="fcc-accordion">
    <? $items = get_field('items');
    foreach ($items as $item) { ?>
      <div class="accordion">
        <div class="accordion-title"><?= $item['title'] ?></div>
        <div class="accordion-content"><?= $item['content'] ?></div>
      </div>
    <? } ?>
  </div>
<? }

function fcc_jobs_render($block, $content, $is_preview, $post_id, $wp_block) {
  if ($is_preview) {
    echo 'Jobs'; return;
  } 
  
  $jobs = get_field('positions'); 
  $form = get_field('form-shortcode'); ?>

  <div class="fcc-jobs">
    <table cellspacing="0" cellpadding="0">
      <tr>
        <th>Job Title</th>
        <th>Job Description</th>
        <?= $form ? '<th>Application</th>' : '' ?>
      </tr>

      <? foreach ($jobs as $job) { ?>
        <tr>
          <td><?= $job['title'] ?></td>
          <td><a href="<?= $job['description']['url'] ?>" target="_blank" class="btn btn-yellow">Download Now</a></td>
          <?= $form ? '<td><a href="#" class="btn btn-yellow fcc-jobs-apply" data-position="' . $job['title'] . '">Apply Now</a></td>' : '' ?>
        </tr>
      <? } ?>
    </table>

    <? if ($form): ?>
      <div class="title-dark">Submit your CV</div>

      <?= get_field('form-description') ?>

      <?= do_shortcode($form) ?>
    <? endif; ?>
  </div>
<? }

function fcc_partner_clubs_render($block, $content, $is_preview, $post_id, $wp_block) {
  if ($is_preview) {
    echo 'Reciprocal Clubs'; return;
  }

  $all = !empty(get_field('all'));
  $clubs = get_posts([
    'post_type' => 'partner-club',
    'posts_per_page' => -1,
    'meta_key' => 'wpcf-country',
    'orderby' => 'meta_value date',
    'order' => 'ASC',
  ]); ?>

  <div class="fcc-partner-clubs">
    <input type="text" placeholder="Search..">

    <table cellspacing="0" cellpadding="0">
      <tr>
        <? if ($all) { ?><th>Code</th><? } ?>
        <th>Country</th>
        <th>Club Name</th>
        <? if (!$all) { ?>
          <th>Website</th>
        <? } else { ?>
          <th>Address / Tel / Fax / Email / Website</th>
          <th>Contact Person / Email</th>
          <th>Extra</th>
        <? } ?>
      </tr>

      <? foreach ($clubs as $club) { ?>
        <tr>
          <? if ($all) { ?><td class="fcc-partner-clubs-code"><?= get_field('wpcf-code', $club->ID) ?></td><? } ?>
          <td class="fcc-partner-clubs-country"><?= get_field('wpcf-country', $club->ID) ?></td>
          <td class="fcc-partner-clubs-title"><?= $club->post_title ?></td>

          <? if (!$all) { 
            echo "<td class='fcc-partner-clubs-website'>";
            $lines = explode("\n", strip_tags(get_field('wpcf-contact-information', $club->ID)));
            foreach ($lines as $line) {
              $line = trim($line);
              if (strpos($line, ' ') === false && strpos($line, ".") !== false && strpos($line, '@') === false) {
                if (strpos($line, '://') === false)
                  $line = 'http://' . $line;
                echo "<a href='$line' target='_blank'>$line</a><br/>";
              }
            }
            echo "</td>";
          } else { ?>
            <td><?= nl2br(get_field('wpcf-contact-information', $club->ID, false)) ?></td>
            <td><?= nl2br(get_field('wpcf-contact-person', $club->ID, false)) ?></td>
            <td>
              <? $info = get_field('wpcf-introductory-card', $club->ID);
              if ($info) { ?><b>Intro Card</b><br/><?= $info ?></br> <? } ?>
              
              <? $info = get_field('wpcf-guest-allowed', $club->ID);
              if ($info) { ?><b>No of Guest Allowed</b><br/><?= $info ?></br> <? } ?>
              
              <? $info = get_field('wpcf-payment-methods', $club->ID);
              if ($info) { ?><b>Payment Methods</b><br/><?= $info ?></br> <? } ?>

              <? 
              global $fac_options;
              $fac_options = [
                'dining' => 'Dining',
                'outdoor' => 'Outdoor Terrace Dining',
                'function' => 'Function Room',
                'conference' => 'Conference',
                'accommodation' => 'Accommodation',
                'parking' => 'Parking',
                'healthclub' => 'Health Club',
                'swimming' => 'Swimming Pool',
                'library' => 'Library',
                'workroom' => 'Workroom',
                'wifi' => 'WIFI',
                'internet' => 'Internet',
              ]; 
              
              $facilities = get_field('wpcf-facilities', $club->ID);
              $facilities = array_reduce((array)$facilities, function($carry, $facility) {
                global $fac_options;
                if (is_array($facility))
                  $carry[] = $fac_options[$facility[0]];
                else
                  $carry[] = $fac_options[$facility];

                return $carry;
              }, []);
              $info = implode(', ', $facilities); 
              if ($info) { ?><b>Facilities</b><br/><?= $info ?></br> <? } ?>
            </td>
          <? } ?>
        </tr>
      <? } ?>
    </table>
  </div>
<? }

function echo_cwm_render($block, $content, $is_preview, $post_id, $wp_block) {
  if ($is_preview) {
    echo 'Content with Menu'; return;
  }
  
  $items = get_field('items'); ?>

  <div class="echo-cwm">
    <div class="echo-cwm-inner">
      <div class="echo-cwm-menu">
        <? foreach ($items as $index => $item) { ?>
          <a href="#" data-index="<?= $index ?>" class="<?= $index == 0 ? 'selected' : '' ?>"><?= $item['label'] ?></a>
        <? } ?>
      </div>

      <div class="echo-cwm-content">
        <? foreach ($items as $index => $item) { ?>
          <div data-index="<?= $index ?>" class="<?= $index == 0 ? 'selected' : '' ?>">
            <div class="title-light"><?= $item['title'] ?></div>
            <?= $item['content'] ?>
          </div>
        <? } ?>
      </div>
    </div>
  </div>
<? }
  
function echo_banner_render($block, $content, $is_preview, $post_id, $wp_block) {
  if ($is_preview) {
    echo 'Banner'; return;
  }
  
  $bg = get_field('bg');
  $title = get_field('title');
  $description = get_field('description');
  $button = get_field('button'); ?>

  <div class="echo-banner" data-parallax="scroll" data-image-src="<?= $bg['sizes']['large'] ?>">
    <? if ($description || $button) { ?>
      <div class="title-light alignleft"><?= nl2br($title) ?></div>
    <? } else { ?>
      <div class="echo-banner-title"><?= nl2br($title) ?></div>
    <? } ?>

    <? if ($description) { ?><div class="echo-banner-desc"><?= nl2br($description) ?></div><? } ?>
    <? if ($button) { ?><a class="echo-banner-btn btn btn-yellow" href="<?= $button['url'] ?>"><?= $button['title'] ?></a><? } ?>
  </div>
<? }