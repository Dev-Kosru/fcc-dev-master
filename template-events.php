<?php
/**
 * Template Name: Events
 */
?>

<? $term = get_field('events_category');
$slider = get_field('page-settings-slider'); ?>

<?php get_header() ?>

<main class="page-events">
  <?
  if (is_array($slider) && count($slider) > 0) 
    slider_render($slider);
  else { ?>
    <div class="carousel page-events-featured fcc-carousel large-dots alignfull" data-flickity='{ "lazyLoad": 1, "cellAlign": "center", "contain": true, "adaptiveHeight": false, "autoPlay": 8000, "imagesLoaded": true, "prevNextButtons": false, "fade": true }'>
      <? $featured = get_field('featured_events');
      if ($featured)
        foreach ($featured as $event) { ?>
          <div class="carousel-cell page-events-featured-slide">
            <div class="cell-inner">
              <img src="<?= get_the_post_thumbnail_url($event, 'medium_large') ?>" />
              <div class="cell-content">
                <div class="page-events-featured-label">
                  <i class="far fa-star"></i> Featured Event
                </div>
                <div class="page-events-featured-title"><?= $event->post_title ?></div>
                <div class="page-events-featured-actions">
                  <a class="btn btn-blue" href="<?= get_permalink($event) ?>">Read More</a>
                  
                  <? if (get_field('event_quota', $event->ID) > 0) {?>
                    <a class="btn btn-yellow" href="/members-area/#/app/events_event/<?= $event->ID ?>">Sign Up</a>
                  <? } ?>
                  
                  <a class="fcc-share" href="<?= get_permalink($event) ?>" title="<?= $event->post_title ?>"><i class="fa fa-share-alt"></i></a>
                </div>
              </div>
            </div>
          </div>
        <?php } ?> 
    </div>
  <? } ?>
  
  <?= fcc_title_bar_render(false, false, false, false, false, false) ?>

  <div class="flex-grid">
    <div class="col-8">
      <?= $term ? "<div class='title-dark'>{$term->name}</div>" : "" ?>
      
      <?= $term ? nl2br($term->description) : the_content() ?>

      <script>eventsCategory = '<?= $term ? $term->slug : 'events' ?>';</script>

      <div id="react-app" style="position: relative;"></div>
      <script type="text/javascript" src="<?= get_template_directory_uri() ?>/events.js"></script>
    </div>
    <div class="col-1"></div>
    <div class="col-3 sidebar">
      <?= do_shortcode(get_field('page-settings-sidebar')); ?>
      <?php dynamic_sidebar('sidebar-extras'); ?>
    </div>
  </div>
</main>

<?php get_footer(); ?>