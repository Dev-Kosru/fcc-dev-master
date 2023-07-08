<?php
require_once ABSPATH . 'wp-admin/includes/class-walker-category-checklist.php';

include 'functions-api.php';
include 'functions-email.php';
include 'functions-admin.php';
include 'functions-types.php';
include 'functions-blocks-render.php';
include 'functions-blocks.php';
include 'functions-sidebar.php';
include 'functions-shop.php';

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style( 'style', get_stylesheet_uri(), array() );
	wp_enqueue_script( 'script', get_stylesheet_directory_uri() . '/js/script.js', ['jquery'] );
	wp_enqueue_script( 'flickity', get_stylesheet_directory_uri() . '/js/flickity.js', array() );
	wp_enqueue_script( 'parallax', get_stylesheet_directory_uri() . '/js/parallax.min.js', ['jquery'] );
	wp_enqueue_script( 'flatpickr', get_stylesheet_directory_uri() . '/js/flatpickr.js', ['jquery'] );
	wp_enqueue_script( 'gdpr', get_stylesheet_directory_uri() . '/js/gdpr.js', [] );
});

add_action( 'after_setup_theme', function() {
  register_nav_menus([
    'primary' => 'Primary',
    'footer' => 'Footer',
  ]);

  add_theme_support(
    'html5',
    [
      'search-form',
      'comment-form',
      'comment-list',
      'gallery',
      'caption',
      'style',
      'script',
    ]
  );
  add_theme_support('post-thumbnails');
  add_theme_support('align-wide');
  add_theme_support('woocommerce');
  add_theme_support( 'editor-styles' );
  add_theme_support( 'editor-color-palette', [
		[
			'name'  => 'Blue',
			'slug'  => 'blue',
			'color'	=> '#034663',
    ],
		[
			'name'  => 'Yellow',
			'slug'  => 'yellow',
			'color' => '#fdc716',
    ],
    [
      'name'	=> 'Light-Grey',
      'slug'	=> 'light_grey',
      'color'	=> '#f6f7f8',
    ],
    [
      'name'	=> 'Dark-Grey',
      'slug'	=> 'dark_grey',
      'color'	=> '#adb0b1',
    ],
		[
			'name'	=> 'White',
			'slug'	=> 'white',
			'color'	=> '#ffffff',
    ],
		[
			'name'	=> 'Black',
			'slug'	=> 'black',
			'color'	=> '#000000',
    ],
  ]);
});

add_action( 'parse_query', function() {
  remove_filter( 'template_redirect', 'redirect_canonical' );
});

add_action('template_redirect', function() {
  $redirect = get_field('redirect_url');

  if ($redirect) {
    wp_redirect($redirect);
    exit();
  }
});

add_filter( 'widget_text', 'do_shortcode' );

add_action( 'widgets_init', function() {
  register_sidebar([
    'name'          => 'Footer Column 1',
    'id'            => 'footer-1',
    'description'   => 'Footer widget area 1',
  ]);
  register_sidebar([
    'name'          => 'Footer Column 2',
    'id'            => 'footer-2',
    'description'   => 'Footer widget area 2',
  ]);
  register_sidebar([
    'name'          => 'Footer Column 3',
    'id'            => 'footer-3',
    'description'   => 'Footer widget area 3',
  ]);
  register_sidebar([
    'name'          => 'Footer Column 4',
    'id'            => 'footer-4',
    'description'   => 'Footer widget area 4',
  ]);
  register_sidebar([
    'name'          => 'Global Sidebar Content',
    'id'            => 'sidebar-extras',
    'description'   => 'Content in the sidebar for all pages',
  ]);
});

add_shortcode('search', function( $form ) {
  ob_start();
  get_search_form();
  return ob_get_clean();
});

add_shortcode('embed', function( $form ) {
  return '';
});
add_shortcode('caption', function( $atts, $content ) {
  return "<div class='fcc-caption'>" . $content . "</div>";
});

add_filter('tiny_mce_before_init', function($init) {
  $custom_colours = '
      "3366FF", "Color 1 name",
      "CCFFCC", "Color 2 name",
      "FFFF00", "Color 3 name",
      "99CC00", "Color 4 name",
      "FF0000", "Color 5 name",
      "FF99CC", "Color 6 name",
      "CCFFFF", "Color 7 name"
  ';
  $init['textcolor_map'] = '[
    "034663", "Blue",
    "fdc716", "Yellow",
    "f6f7f8", "Light-Grey",
    "adb0b1", "Dark-Grey",
    "ffffff", "White",
    "000000", "Black"
  ]';
  $init['textcolor_rows'] = 1;
  return $init;
});

add_filter( 'template_include', function(string $template) {
  global $post;
  $pages = get_option('fcc_pages');
  $pages = unserialize($pages);
  if (!$post) return $template;

  foreach ($pages as $name => $id) {
    if ($id == $post->ID)
      return __DIR__ . "/page-$name.php";
  }

  return $template;
});

class FCC_Walker_Category_Checklist extends Walker_Category_Checklist {
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
  	$taxonomy = $args['taxonomy'];
		if ( 'category' === $taxonomy ) {
			$name = 'post_category';
		} else {
			$name = 'tax_input[' . $taxonomy . ']';
		}

		$args['selected_cats'] = empty( $args['selected_cats'] ) ? array() : $args['selected_cats'];
    $is_selected = in_array( $category->slug, $args['selected_cats'], true );

    $output .= "\n<li id='{$taxonomy}-{$category->slug}'>" .
      '<label class="selectit"><input value="' . $category->slug . '" type="checkbox" name="' . $name . '[]" id="in-' . $taxonomy . '-' . $category->slug . '"' .
      checked( $is_selected, true, false ) . ' /> ' .
      '<span class="checkmark"></span>' .
      $category->name . '</label>';
  }
}

function fcc_terms_checklist($params = array() ) {
  $taxonomy = $params['taxonomy'] ?: 'category';

  $categories = (array) get_terms( [
    'taxonomy' => $taxonomy,
    'exclude' => '1',
    'hide_empty' => false,
    'parent' => $params['child_of'] ?: 0, // non-hierarchically
  ] );
  usort($categories, function($a, $b) {
    return (int)get_field('order', "event-categories_{$a->term_id}") - (int)get_field('order', "event-categories_{$b->term_id}");
  });

  $output = '';
  // non-hierarchically:
  $name = 'category' === $taxonomy ? 'post_category' : 'tax_input[' . $taxonomy . ']';
  foreach ($categories as $category) {
    $output .= "\n<li id='{$taxonomy}-{$category->term_id}'>" .
      '<label class="selectit"><input value="' . $category->slug . '" type="checkbox" name="' . $name . '[]" id="in-' . $taxonomy . '-' . $category->slug . '"' .
      checked(in_array($category->slug, $params['selected_cats'] ?: [], true ), true, false ) . ' /> ' .
      '<span class="checkmark"></span>' .
      $category->name . '</label></li>';
  }

  // hierarchically:
  // $output .= $walker->walk($categories, 0, $args);

  echo $output;

  return $output;
}

$spinner = "<paper-spinner id='loadingIndicator' active='' class='style-scope sj-search-box x-scope paper-spinner-0'>
  <div id='spinnerContainer' class='active  style-scope paper-spinner'>
    <div class='spinner-layer layer-1 style-scope paper-spinner'>
      <div class='circle-clipper left style-scope paper-spinner'>
        <div class='circle style-scope paper-spinner'></div>
      </div><div class='gap-patch style-scope paper-spinner'>
        <div class='circle style-scope paper-spinner'></div>
      </div><div class='circle-clipper right style-scope paper-spinner'>
        <div class='circle style-scope paper-spinner'></div>
      </div>
    </div>
  </div>
  </paper-spinner>";

function fcc_event_date($event) {
  $start = strtotime(get_field('_event_start_local', $event->ID, false));
  $end = strtotime(get_field('_event_end_local', $event->ID, false));
  $all_day = get_field('_event_all_day', $event->ID);

  $start_date = date("j M Y", $start);
  $end_date = date("j M Y", $end);
  $start_time = date("h:i A", $start);
  $end_time = date("h:i A", $end);

  if ($all_day || $start_time == $end_time) {
    if ($start == $end)
      return $start_date . ($all_day ? '' : ' ' . $start_time);
    else
      return $start_date . " — " . $end_date;
  } else
    return $start_date . " " . $start_time . " — " . $end_time;
}

function fcc_event_start_timestamp($event) {
  $start = get_field('_event_start_local', $event->ID, false);
  $all_day = get_field('_event_all_day', $event->ID);

  return strtotime($all_day ? explode(' ', $start)[0] . ' 00:00:00' : $start);
}

function fcc_event_end_timestamp($event) {
  $end = get_field('_event_end_local', $event->ID, false);
  $all_day = get_field('_event_all_day', $event->ID);

  return strtotime($all_day ? explode(' ', $end)[0] . ' 00:00:00' : $end);
}

// Extended Meta Key Search In WP_Query
add_action('pre_get_posts', function ($q) {
  // Check the meta query:
  $mq = $q->get('meta_query');

  if (empty($mq))
    return;

  // Init:
  $marker = '___tmp_marker___';
  $rx     = [];

  // Collect all the sub meta queries, that use REGEXP, RLIKE or LIKE:
  foreach ($mq as $k => $m) {
    if (
      isset($m['_key_compare'])
      && in_array(strtoupper($m['_key_compare']), ['REGEXP', 'RLIKE', 'LIKE'])
      && isset($m['key'])
    ) {
      // Mark the key with a unique string to secure the later replacements:
      $m['key'] .= $marker . $k; // Make the appended tmp marker unique

      // Modify the corresponding original query variable:
      $q->query_vars['meta_query'][$k]['key'] = $m['key'];

      // Collect it:
      $rx[$k] = $m;
    }
  }

  // Nothing to do:
  if (empty($rx))
    return;

  // Get access the generated SQL of the meta query:
  add_filter('get_meta_sql', function ($sql) use ($rx, $marker) {
    // Only run once:
    static $nr = 0;
    if (0 != $nr++)
      return $sql;

    // Modify WHERE part where we replace the temporary markers:
    foreach ($rx as $k => $r) {
      $sql['where'] = str_replace(
        sprintf(
          ".meta_key = '%s' ",
          $r['key']
        ),
        sprintf(
          ".meta_key %s '%s' ",
          $r['_key_compare'],
          str_replace(
            $marker . $k,
            '',
            $r['key']
          )
        ),
        $sql['where']
      );
    }
    return $sql;
  });
});

add_filter('wpcf7_special_mail_tags', function($null, $tag_name, $html, $mail_tag) {
  if (strpos($tag_name, 'get-') === 0) {
    $parts = parse_url($_SERVER['HTTP_REFERER']);
    parse_str($parts['query'], $query);

    return sanitize($query[substr($tag_name, 4)]);
  }

  return null;
}, 10, 4);

function slider_render($slider) {
  if (is_array($slider)) {
    if (count($slider) > 1) { ?>
      <div class="carousel featured-image" data-flickity='{ "lazyLoad": 1, "cellAlign": "center", "contain": true, "adaptiveHeight": true, "autoPlay": 6000, "imagesLoaded": true, "pageDots": false}'>
        <?php foreach ($slider as $image) { ?>
          <div class="carousel-cell">
            <img src="<?= is_string($image) ? $image : $image['sizes']['1536x1536'] ?>" />
          </div>
        <?php } ?>
      </div>
    <? } else { ?>
      <div class="featured-image-single" data-parallax="scroll" data-image-src="<?= $slider[0]['sizes']['1536x1536'] ?>"></div>
    <? }
  }
}

//open graph
add_filter('language_attributes', function( $output ) {
  return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
});