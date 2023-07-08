<?php
/**
 * Template Name: Bare Template
 */
?>

<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>The Foreign Correspondents' Club Hong Kong</title>
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="shortcut icon" href="<?= get_stylesheet_directory_uri() ?>/images/favicon.png">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <div id="loader" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: white; z-index: 100;"></div>
  <?php wp_body_open(); ?>

  <div id="page" class="site">
    <? the_content() ?>
  </div>

  <?php wp_footer(); ?>
</body>
</html>
