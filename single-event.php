<? get_header() ?>

<main>
  <div class="fcc-title-bar">
    <? $term = get_the_terms($post->ID, 'event-categories')[0] ?>
    <div class="fcc-title-bar-title"><?= strpos($term->name, 'Promotion') !== false ? '' : '<span><a href="/events">Events</a></span>' ?><span><a href="/events?cats=<?= $term->term_id ?>"><?= $term->name ?></a></span><span><?= mb_strimwidth(trim(do_shortcode(get_the_title())), 0, 30, '...') ?></span></div>
  </div>

  <div class="flex-grid">
    <div class="col-8">
      <div id="content" class="single-view">
        <img src="<?= get_the_post_thumbnail_url($featured, 'medium_large') ?>" />
        <h1><?= get_the_title() ?></h1>
        <div class="single-event-info">Date: <?= fcc_event_date($post) ?> | Venue: <?= implode(', ', (array)get_field('event_venue')) ?></div>

        <hr/>

        <div class="single-event-calendars">
          <a class="btn btn-yellow" target="_blank" href="/wp-json/fcc/v1/event_cal?id=<?= get_the_ID() ?>"><i class="far fa-calendar-plus"></i> Add to iCalendar</a>
          <a class="btn btn-yellow" target="_blank" href="http://www.google.com/calendar/event?action=TEMPLATE&text=<?= urlencode(get_the_title()) ?>&dates=<?
            $start_date = strtotime((string)str_replace('/', '-', (string)get_field('_event_start_local', false, false)));
            echo date("Ymd\THis\Z", $start_date - 8*60*60);
          ?>/<?
            $end_date = strtotime((string)str_replace('/', '-', (string)get_field('_event_end_local', false, false)));
            echo date("Ymd\THis\Z", $end_date - 8*60*60);
          ?>&location=Foreign%20Correspondents%20Club%20-%20<?= get_field('event_venue') ?>"><i class="far fa-calendar-plus"></i> Add to Google Calendar</a>
        </div>
        
        <div class="single-event-desc">
          <?= preg_replace('/ width: \d+px/', '', preg_replace('/ width="\d+"/', '', apply_filters('the_content', get_the_content()))) ?>
        </div>

        <? $video = get_post_meta(get_the_ID(), 'wpcf-video', true);
        if (!empty($video)) { ?>
          <iframe width="560" height="315" src="<?= $video ?>" frameborder="0" allowfullscreen=""></iframe>
        <? } ?>

        <? $gallery = get_field('event_gallery') ?: get_post_meta(get_the_ID(), 'wpcf-image');
        if (!empty($gallery) && count(array_filter($gallery, function($item) { return !empty($item); })))
          fcc_gallery_preview_render(false, false, false, false, false, false, $gallery, true, false);

        ?>
      </div>
    </div>
    <div class="col-1"></div>
    <div class="col-3 sidebar">
      <? do_shortcode('[news]') ?>
      <? do_shortcode('[speakers]') ?>
      <?= fcc_correspondent_preview_render(false, false, false, false, false) ?>
      <? dynamic_sidebar('sidebar-extras'); ?>
    </div>
  </div>
</main>

<? get_footer(); ?>