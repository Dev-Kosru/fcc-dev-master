<?php 
$paged = $_GET['paged'] ?: 1;
$cat = empty($_GET['category']) ? null : explode(',', $_GET['category']);
$date = sanitize_text_field($_GET['date']);
$search = isset($_GET['search']) ? $_GET['search'] : null;
$view = sanitize_text_field($_GET['view']) ?: 'grid';

if ($view == 'calendar') {
  $ends_after = substr($date, 0, 7);
  $starts_before = substr($date, 0, 7) . '-31';
} else {
  if ($_GET['past']) {
    $starts_before =  date("Y-m-d 00:00:00");
    $ends_after = "0000-00-00 00:00:00";
  } else {
    $starts_before = "9999-99-99 99:99:99";
    $ends_after = $date ?: date("Y-m-d 00:00:00");
  }
}

get_header() ?>

<main class="page-events">
  <div class="carousel page-events-featured fcc-carousel large-dots alignfull" data-flickity='{ "lazyLoad": 1, "cellAlign": "center", "contain": true, "adaptiveHeight": false, "autoPlay": 8000, "imagesLoaded": true, "prevNextButtons": false, "fade": true }'>
    <? $featured = get_posts([ 
      'post_type' => 'event',
      'meta_key' => 'featured',
      'meta_value' => '1',
      'posts_per_page' => 5
    ]); 
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
              <a class="btn btn-yellow" href="/members-area/#/app/events_event/<?= $event->ID ?>">Sign Up</a>
              <a class="fcc-share" href="<?= get_permalink($event) ?>" title="<?= $event->post_title ?>"><i class="fa fa-share-alt"></i></a>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>

  <?= fcc_title_bar_render() ?>

  <div class="flex-grid">
    <div class="col-8">
      <?= the_content() ?>

      <div class="page-actions">
        <div class="page-actions-section page-actions-filters">
          <div class="filter">
            <div class="label"><i class="fa fa-filter"></i><span class="label">Filter</span></div>
            <div class="filter-content">
              <? fcc_terms_checklist(['post_type' => 'event', 'taxonomy' => 'event-categories', 'selected_cats' => $cat]) ?>
            </div>
          </div>
          <div class="filter">
            <div class="label"><i class="fa fa-search"></i><span class="label">Search</span></div>
            <div class="filter-content">
              <input class="filter-search-input" type="text" size="20">
            </div>
          </div>
        </div>
        <div class="page-actions-section page-actions-date">
          <i class="fa fa-chevron-left date-prev"></i>
          <input type="text" size="0" data-input readonly>
          <div class="date-current"></div>
          <i class="fa fa-chevron-right date-next"></i>
          <i class="fas fa-calendar-day date-calendar" data-toggle></i>
        </div>
        <div class="page-actions-section page-actions-view">
          <div class="list-view <?= $view == 'list' ? 'active' : '' ?>"><i class="fas fa-bars"></i><span>List</span></div>
          <div class="grid-view <?= $view == 'grid' ? 'active' : '' ?>"><i class="fas fa-th-large"></i><span>Grid</span></div>
          <div class="calendar-view <?= $view == 'calendar' ? 'active' : '' ?>"><i class="far fa-calendar-alt"></i><span>Calendar</span></div>
        </div>
      </div>

      <div id="content" class="<?= $view ?>-view">
        <?php
        $tax_query = [
          [
            'taxonomy' => 'event-categories',
            'field' => 'slug',
            'terms' => ['club-events', 'music', 'private-event', 'speakers', 'sports-schedule', 'wall-exhibition'],
          ],
        ];
        if (!empty($cat))
          $tax_query[] = [
            'taxonomy' => 'event-categories',
            'terms' => $cat,
            'field' => 'slug',
          ];

        $posts = get_posts([
          'post_type' => 'event',
          'posts_per_page' => $view == 'calendar' ? -1 : 10,
          'paged' => $paged,
          's' => $search,
          'meta_query' => [
            'relation' => 'AND',
            'start' => [
              'key' => '_event_start_local',
              'value' => $starts_before,
              'compare' => '<=', 
            ],
            'end' => [
              'key' => '_event_end_local',
              'value' => $ends_after,
              'compare' => '>=', 
            ],
          ],
          'orderby' => [
            'start' => $_GET['past'] ? 'DESC' : 'ASC'
          ],
          'tax_query' => $tax_query,
        ]);

        if ($view == 'calendar') {
          $time = strtotime($ends_after);
          $year = date('Y', $time);
          $month = date('n', $time);
          $first_of_month = gmmktime(0, 0, 0, $month, 1, $year);
          $days_in_month = gmdate('t', $first_of_month);
          $days = [];
          for ($i = 1; $i <= $days_in_month; $i++) {
            // $content = "<div class='day'>$i</div>";
            $content = [];
            $prev = $i - 1;
            $starts_before = date("Y-m-d 00:00:00", strtotime("+$i day", $first_of_month));
            $ends_after = date("Y-m-d 00:00:00", strtotime("+$prev day", $first_of_month));
            foreach ($posts as $post) {
              if (get_post_meta($post->ID, '_event_start_local', true) < $starts_before && get_post_meta($post->ID, '_event_end_local', true) >= $ends_after) {
                $title = str_replace("'", "&#39;", $post->post_title);
                $thumb = get_the_post_thumbnail_url($item);
                $thumb = $thumb ? "<img src='$thumb'/>" : "";
                $content[] = "
                  <a href='". get_permalink($post) . "' title='$title' class='cal-event'>
                    $post->post_title
                    <div class='cal-popup'>
                      $thumb
                      <div class='cal-title'>$post->post_title</div>
                      <div class='time'>" . fcc_event_date($item, true) . "</div>
                    </div>  
                  </a>
                ";
              }
            }

            $days[$i] = $content;
          }

          echo generate_calendar($year, $month, $days, 3, null, 1);
        
        } else {
          foreach ($posts as $item) { ?>
            <div class="fcc-preview">
              <div class="fcc-preview-compact">
                <a href="<?= get_permalink($item) ?>" class="fcc-preview-image">
                  <img src="<?= get_the_post_thumbnail_url($item, 'medium_large') ?>" />
                </a>
                <div class="fcc-preview-content">
                  <div class="fcc-preview-title"><a href="<?= get_permalink($item) ?>"><?= $item->post_title ?></a></div>
                  <div class="fcc-preview-date"><?= fcc_event_date($item) ?></div>
                </div>
              </div>
              <div class="fcc-preview-desc"><?= mb_strimwidth(trim(strip_tags(do_shortcode($item->post_content))), 0, 300, '...') ?></div>
              <div class="fcc-preview-actions">
                <a class="btn btn-yellow" href="<?= get_permalink($item) ?>">Read More</a>
                <a class="btn btn-blue" href="/members-area/#/app/events_event/<?= $item->ID ?>">Sign Up</a>
                <a class="fcc-share" href="<?= get_permalink($item) ?>" title="<?= $item->post_title ?>"><i class="fa fa-share-alt"></i></a>
              </div>  
            </div>
          <?php } 
        } ?>
      </div>

      <div id="load-more" class="<?= !count($posts) || $view == 'calendar' ? 'empty' : '' ?>">
        <div class="btn btn-white">Load More</div>
        <div class="empty-text">No results</div>
        <?= $spinner ?>
      </div>
    </div>
    <div class="col-1"></div>
    <div class="col-3 sidebar">
      <?= do_shortcode(get_field('page-settings-sidebar')); ?>
      <?php dynamic_sidebar('sidebar-extras'); ?>
    </div>
  </div>
</main>

<?php get_footer();

function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array())
{
  $first_of_month = gmmktime(0, 0, 0, $month, 1, $year);
  // remember that mktime will automatically correct if invalid dates are entered
  // for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
  // this provides a built in "rounding" feature to generate_calendar()

  $day_names = array(); //generate all the day names according to the current locale
  for ($n = 0, $t = (3 + $first_day) * 86400; $n < 7; $n++, $t += 86400) //January 4, 1970 was a Sunday
    $day_names[$n] = ucfirst(gmstrftime('%A', $t)); //%A means full textual day name

  list($month, $year, $month_name, $weekday) = explode(',', gmstrftime('%m, %Y, %B, %w', $first_of_month));
  $weekday = ($weekday + 7 - $first_day) % 7; //adjust for $first_day
  $calendar = "<div class=\"mini_calendar\">\n<table>" . "\n<tr>";

  if ($day_name_length) {   //if the day names should be shown ($day_name_length > 0)
    //if day_name_length is >3, the full name of the day will be printed
    foreach ($day_names as $d)
      $calendar  .= '<th abbr="' . htmlentities($d) . '">' . htmlentities($day_name_length < 4 ? substr($d, 0, $day_name_length) : $d) . '</th>';
    $calendar  .= "</tr>\n<tr>";
  }

  $days_in_month = gmdate('t', $first_of_month);
  
  $weeks = ceil(($days_in_month + $weekday) / 7);
  for ($week = 0; $week < $weeks; $week++) {
    $startDay = -$weekday + 1 + $week * 7;
    $maxEvents = 0;
    for ($day = $startDay; $day < $startDay + 7; $day++) {
      $maxEvents = max($maxEvents, isset($days[$day]) ? count($days[$day]) : 0);
    }
    $maxEvents = max(2, $maxEvents);
    $calendar .= "<tr class='cal-dates'>";
    for ($day = $startDay; $day < $startDay + 7; $day++) {
      $date = $day > 0 && $day <= $days_in_month ? $day : '';
      $calendar .= "<td>{$date}</td>";
    }
    $calendar .= "</tr>";
    for ($row = 0; $row < $maxEvents; $row++) {
      $calendar .= "<tr>";
      $day = $startDay; 
      while($day < $startDay + 7) {
        $content = is_array($days[$day]) ? array_shift($days[$day]) : '';
        $day++;
        $colspan = 1;
        if ($content) {
          while ($day < $startDay + 7 && $days[$day][0] == $content) {
            array_shift($days[$day]);
            $colspan++;
            $day++;
          }
        }
        $calendar .= "<td colspan={$colspan}>{$content}</td>";
      }
      $calendar .= "</tr>";
    }
  }

  return $calendar . "</table>\n</div>\n";
}
?>